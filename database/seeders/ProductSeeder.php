<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private const IMAGE_POOL = [
        'products/nike-air-zoom-pegasus-40.jpg',
        'products/nike-air-force-1-low.jpg',
        'products/nike-lebron-xx.jpg',
        'products/nike-metcon-9.jpg',
        'products/nike-kyrie-infinity.jpg',
        'products/nike-court-vision-low.jpg',
        'products/nike-flex-experience-12.jpg',
        'products/adidas-ultraboost-light.jpg',
        'products/adidas-samba-og.jpg',
        'products/adidas-predator-edge.jpg',
        'products/adidas-ultraboost-23.jpg',
        'products/adidas-crazyflight.jpg',
        'products/adidas-gamecourt-2.jpg',
        'products/puma-rs-x.jpg',
        'products/puma-future-9.jpg',
        'products/puma-ignite-elevate.jpg',
        'products/vans-old-skool.jpg',
        'products/vans-ultrarange-rapidweld.jpg',
        'products/converse-chuck-70-premium.jpg',
        'products/converse-run-star-hike.jpg',
    ];

    private const COLORS = [
        'Black' => '#000000',
        'White' => '#FFFFFF',
        'Red' => '#EF4444',
        'Blue' => '#2563EB',
        'Grey' => '#6B7280',
        'Green' => '#16A34A',
        'Beige' => '#D6C7A1',
        'Navy' => '#1E3A5F',
        'Pink' => '#EC4899',
        'Cream' => '#FFF4D6',
    ];

    public function run(): void
    {
        $brands = Brand::query()->pluck('id', 'name');
        $categories = Category::query()->pluck('id', 'name');
        $catalog = $this->catalog();
        $now = now();

        foreach ($catalog as $index => $data) {
            $slug = Str::slug($data['name']);
            $price = $data['price'];
            $salePrice = $index % 3 === 0
                ? (int) (floor(($price * .82) / 10000) * 10000)
                : null;

            $product = Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $categories[$data['category']],
                'brand_id' => $brands[$data['brand']],
                'name' => $data['name'],
                'description' => $this->description($data['name'], $data['brand'], $data['category']),
                'price' => $price,
                'sale_price' => $salePrice,
                'thumbnail' => self::IMAGE_POOL[$index % count(self::IMAGE_POOL)],
                'status' => true,
                'featured' => $index % 4 === 0,
                'created_at' => $now->copy()->subDays(count($catalog) - $index - 1),
                'updated_at' => $now,
            ]);

            $this->seedImages($product, $index);
            $this->seedVariants($product, $data['category'], $index);
        }
    }

    private function seedImages(Product $product, int $productIndex): void
    {
        $imageCount = count(self::IMAGE_POOL);

        foreach ([1, 7, 13] as $sortOrder => $offset) {
            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => $sortOrder + 1],
                ['image' => self::IMAGE_POOL[($productIndex + $offset) % $imageCount]]
            );
        }

        $product->images()->where('sort_order', '>', 3)->delete();
    }

    private function seedVariants(Product $product, string $category, int $productIndex): void
    {
        $sizes = $this->sizesFor($category, $productIndex);
        $colorCount = 2 + ($productIndex % 3);
        $accentColors = array_slice(array_keys(self::COLORS), 2);
        $colors = ['Black', 'White'];

        for ($i = 0; $i < $colorCount - 2; $i++) {
            $colors[] = $accentColors[($productIndex + $i) % count($accentColors)];
        }

        $expectedSkus = [];

        foreach ($sizes as $sizeIndex => $size) {
            foreach ($colors as $colorIndex => $colorName) {
                $sku = sprintf('SNK-%03d-%s-%s', $productIndex + 1, $size, Str::upper(Str::substr($colorName, 0, 3)));
                $stockSeed = (($productIndex + 1) * 37) + ($sizeIndex * 11) + ($colorIndex * 7);
                $bucket = $stockSeed % 20;
                $stock = match (true) {
                    $bucket < 2 => 0,
                    $bucket < 5 => 1 + ($stockSeed % 4),
                    default => 5 + ($stockSeed % 26),
                };

                $variant = ProductVariant::withTrashed()->firstOrNew(['sku' => $sku]);
                $variant->fill([
                    'product_id' => $product->id,
                    'size' => $size,
                    'color_name' => $colorName,
                    'color_code' => self::COLORS[$colorName],
                    'stock' => $stock,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'status' => true,
                ]);
                if ($variant->exists && $variant->trashed()) {
                    $variant->restore();
                }
                $variant->save();

                $expectedSkus[] = $sku;
            }
        }

        $product->variants()->whereNotIn('sku', $expectedSkus)->update(['status' => false]);
    }

    /** @return array<int, string> */
    private function sizesFor(string $category, int $productIndex): array
    {
        $sizes = match ($category) {
            'Giày Bóng Rổ' => ['39', '40', '41', '42', '43', '44', '45'],
            'Giày Lifestyle', 'Giày Skateboarding' => ['36', '37', '38', '39', '40', '41', '42', '43'],
            'Dép & Sandals' => ['36', '37', '38', '39', '40', '41', '42'],
            'Giày Đá Bóng' => ['39', '40', '41', '42', '43', '44'],
            default => ['37', '38', '39', '40', '41', '42', '43', '44'],
        };

        $targetCount = 5 + ($productIndex % 3);
        $selected = array_slice($sizes, 0, min($targetCount, count($sizes)));

        if (in_array('42', $sizes, true) && !in_array('42', $selected, true)) {
            $selected[array_key_last($selected)] = '42';
            sort($selected, SORT_NUMERIC);
        }

        return $selected;
    }

    private function description(string $name, string $brand, string $category): string
    {
        return "{$name} của {$brand} được thiết kế cho dòng {$category}, kết hợp đệm êm, độ bám ổn định và phom ôm chân thoải mái. Phù hợp để vận động thường xuyên và phối đồ hằng ngày.";
    }

    /** @return array<int, array{name: string, brand: string, category: string, price: int}> */
    private function catalog(): array
    {
        return [
            ['name' => 'Nike Air Zoom Pegasus 40', 'brand' => 'Nike', 'category' => 'Giày Chạy Bộ', 'price' => 3590000],
            ['name' => 'Nike Air Force 1 Low', 'brand' => 'Nike', 'category' => 'Giày Lifestyle', 'price' => 3290000],
            ['name' => 'Nike LeBron XXI', 'brand' => 'Nike', 'category' => 'Giày Bóng Rổ', 'price' => 4890000],
            ['name' => 'Nike Mercurial Vapor 15', 'brand' => 'Nike', 'category' => 'Giày Đá Bóng', 'price' => 4290000],
            ['name' => 'Nike Metcon 9', 'brand' => 'Nike', 'category' => 'Giày Tập Gym', 'price' => 3390000],
            ['name' => 'Nike Calm Slide', 'brand' => 'Nike', 'category' => 'Dép & Sandals', 'price' => 1490000],
            ['name' => 'Nike Dunk Low Retro', 'brand' => 'Nike', 'category' => 'Giày Lifestyle', 'price' => 3190000],

            ['name' => 'Adidas Ultraboost Light', 'brand' => 'Adidas', 'category' => 'Giày Chạy Bộ', 'price' => 4590000],
            ['name' => 'Adidas Samba OG', 'brand' => 'Adidas', 'category' => 'Giày Lifestyle', 'price' => 2890000],
            ['name' => 'Adidas Predator Accuracy', 'brand' => 'Adidas', 'category' => 'Giày Đá Bóng', 'price' => 3890000],
            ['name' => 'Adidas Harden Vol 8', 'brand' => 'Adidas', 'category' => 'Giày Bóng Rổ', 'price' => 4190000],
            ['name' => 'Adidas Dropset 3', 'brand' => 'Adidas', 'category' => 'Giày Tập Gym', 'price' => 3290000],
            ['name' => 'Adidas Gamecourt 2', 'brand' => 'Adidas', 'category' => 'Giày Tennis', 'price' => 2090000],
            ['name' => 'Adidas Forum Low', 'brand' => 'Adidas', 'category' => 'Giày Lifestyle', 'price' => 2790000],

            ['name' => 'Air Jordan 1 Low', 'brand' => 'Jordan', 'category' => 'Giày Lifestyle', 'price' => 3490000],
            ['name' => 'Air Jordan 4 Retro', 'brand' => 'Jordan', 'category' => 'Giày Lifestyle', 'price' => 5290000],
            ['name' => 'Jordan Luka 3', 'brand' => 'Jordan', 'category' => 'Giày Bóng Rổ', 'price' => 3690000],
            ['name' => 'Jordan Tatum 2', 'brand' => 'Jordan', 'category' => 'Giày Bóng Rổ', 'price' => 3390000],

            ['name' => 'Converse Chuck 70 Premium', 'brand' => 'Converse', 'category' => 'Giày Lifestyle', 'price' => 2290000],
            ['name' => 'Converse Run Star Hike', 'brand' => 'Converse', 'category' => 'Giày Lifestyle', 'price' => 2690000],
            ['name' => 'Converse Chuck Taylor All Star', 'brand' => 'Converse', 'category' => 'Giày Lifestyle', 'price' => 1790000],
            ['name' => 'Converse Weapon CX', 'brand' => 'Converse', 'category' => 'Giày Bóng Rổ', 'price' => 2990000],

            ['name' => 'Vans Old Skool', 'brand' => 'Vans', 'category' => 'Giày Skateboarding', 'price' => 1890000],
            ['name' => 'Vans Sk8-Hi', 'brand' => 'Vans', 'category' => 'Giày Skateboarding', 'price' => 2190000],
            ['name' => 'Vans Knu Skool', 'brand' => 'Vans', 'category' => 'Giày Skateboarding', 'price' => 2390000],
            ['name' => 'Vans UltraRange EXO', 'brand' => 'Vans', 'category' => 'Giày Năng Động', 'price' => 2490000],

            ['name' => 'Puma RS-X Efekt', 'brand' => 'Puma', 'category' => 'Giày Lifestyle', 'price' => 2690000],
            ['name' => 'Puma Velocity Nitro 3', 'brand' => 'Puma', 'category' => 'Giày Chạy Bộ', 'price' => 3190000],
            ['name' => 'Puma Future 7 Match', 'brand' => 'Puma', 'category' => 'Giày Đá Bóng', 'price' => 2790000],
            ['name' => 'Puma Fuse 3', 'brand' => 'Puma', 'category' => 'Giày Tập Gym', 'price' => 2490000],

            ['name' => 'New Balance Fresh Foam X 1080 v13', 'brand' => 'New Balance', 'category' => 'Giày Chạy Bộ', 'price' => 4290000],
            ['name' => 'New Balance 574 Core', 'brand' => 'New Balance', 'category' => 'Giày Lifestyle', 'price' => 2690000],
            ['name' => 'New Balance 550', 'brand' => 'New Balance', 'category' => 'Giày Lifestyle', 'price' => 3190000],
            ['name' => 'New Balance FuelCell Rebel v4', 'brand' => 'New Balance', 'category' => 'Giày Chạy Bộ', 'price' => 3890000],

            ['name' => 'Asics Gel-Kayano 30', 'brand' => 'Asics', 'category' => 'Giày Chạy Bộ', 'price' => 4490000],
            ['name' => 'Asics Gel-Nimbus 26', 'brand' => 'Asics', 'category' => 'Giày Chạy Bộ', 'price' => 4690000],
            ['name' => 'Asics Gel-Resolution 9', 'brand' => 'Asics', 'category' => 'Giày Tennis', 'price' => 3690000],
            ['name' => 'Asics Japan S', 'brand' => 'Asics', 'category' => 'Giày Lifestyle', 'price' => 2090000],

            ['name' => 'Reebok Classic Leather', 'brand' => 'Reebok', 'category' => 'Giày Lifestyle', 'price' => 2290000],
            ['name' => 'Reebok Club C 85', 'brand' => 'Reebok', 'category' => 'Giày Lifestyle', 'price' => 2190000],
            ['name' => 'Reebok Nano X4', 'brand' => 'Reebok', 'category' => 'Giày Tập Gym', 'price' => 3490000],
            ['name' => 'Reebok Floatride Energy 5', 'brand' => 'Reebok', 'category' => 'Giày Chạy Bộ', 'price' => 2890000],

            ['name' => 'Under Armour HOVR Phantom 3', 'brand' => 'Under Armour', 'category' => 'Giày Chạy Bộ', 'price' => 3790000],
            ['name' => 'Under Armour Curry 11', 'brand' => 'Under Armour', 'category' => 'Giày Bóng Rổ', 'price' => 4390000],
            ['name' => 'Under Armour TriBase Reign 6', 'brand' => 'Under Armour', 'category' => 'Giày Tập Gym', 'price' => 3290000],
            ['name' => 'Under Armour Magnetico Elite 4', 'brand' => 'Under Armour', 'category' => 'Giày Đá Bóng', 'price' => 3990000],

            ['name' => 'Skechers Go Run Ride 11', 'brand' => 'Skechers', 'category' => 'Giày Chạy Bộ', 'price' => 2990000],
            ['name' => 'Skechers Max Cushioning Elite', 'brand' => 'Skechers', 'category' => 'Giày Chạy Bộ', 'price' => 3190000],
            ['name' => 'Skechers Uno Stand on Air', 'brand' => 'Skechers', 'category' => 'Giày Lifestyle', 'price' => 2390000],
            ['name' => 'Skechers Go Walk Arch Fit', 'brand' => 'Skechers', 'category' => 'Giày Năng Động', 'price' => 2590000],
        ];
    }
}
