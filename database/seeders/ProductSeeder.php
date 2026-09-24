<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();

        $sizes = ['39', '40', '41', '42', '43', '44'];
        $colors = [
            ['name' => 'Trắng', 'code' => '#FFFFFF'],
            ['name' => 'Đen', 'code' => '#000000'],
            ['name' => 'Đỏ', 'code' => '#FF0000'],
            ['name' => 'Xanh', 'code' => '#0000FF'],
        ];

        $products = [
            [
                'name' => 'Nike Air Zoom Pegasus 40',
                'category' => 'Giày Chạy Bộ',
                'brand' => 'Nike',
                'price' => 3500000,
                'sale_price' => 2799000,
                'description' => 'Giày chạy bộ Nike Air Zoom Pegasus 40 mang đến sự êm ái và đàn hồi vượt trội. Công nghệ Zoom Air giúp giảm chấn hiệu quả, phù hợp cho chạy bộ đường dài.',
                'featured' => true,
            ],
            [
                'name' => 'Nike Air Force 1 Low',
                'category' => 'Giày Lifestyle',
                'brand' => 'Nike',
                'price' => 3200000,
                'sale_price' => 2590000,
                'description' => 'Giày Nike Air Force 1 Low là biểu tượng thời trang đường phố. Thiết kế cổ điển, chất da cao cấp, phù hợp mọi outfit.',
                'featured' => true,
            ],
            [
                'name' => 'Adidas Ultraboost Light',
                'category' => 'Giày Chạy Bộ',
                'brand' => 'Adidas',
                'price' => 4500000,
                'sale_price' => 3590000,
                'description' => 'Adidas Ultraboost Light với công nghệ đệm Boost nhẹ nhất từ trước đến nay. Đem lại cảm giác chạy nhẹ nhàng và bứt phá.',
                'featured' => true,
            ],
            [
                'name' => 'Adidas Samba OG',
                'category' => 'Giày Lifestyle',
                'brand' => 'Adidas',
                'price' => 2800000,
                'sale_price' => 2190000,
                'description' => 'Adidas Samba OG - huyền thoại giày sân cỏ đã trở thành biểu tượng thời trang. Thiết kế vintage không bao giờ lỗi mốt.',
                'featured' => true,
            ],
            [
                'name' => 'Puma RS-X',
                'category' => 'Giày Năng Động',
                'brand' => 'Puma',
                'price' => 2500000,
                'sale_price' => 1990000,
                'description' => 'Giày Puma RS-X với phong cách retro pha trộn hiện đại. Công nghệ đệm RS (Running System) cho cảm giác êm chân.',
                'featured' => false,
            ],
            [
                'name' => 'Vans Old Skool',
                'category' => 'Giày Lifestyle',
                'brand' => 'Vans',
                'price' => 1800000,
                'sale_price' => null,
                'description' => 'Vans Old Skool - giày trượt ván huyền thoại với sọc Sidestripe đặc trưng. Chất liệu lót bông êm ái.',
                'featured' => true,
            ],
            [
                'name' => 'Converse Chuck 70 Premium',
                'category' => 'Giày Lifestyle',
                'brand' => 'Converse',
                'price' => 2200000,
                'sale_price' => 1890000,
                'description' => 'Converse Chuck 70 là phiên bản cao cấp của Chuck Taylor. Canvas dày hơn, đệm OrthoLite thoải mái hơn.',
                'featured' => false,
            ],
            [
                'name' => 'Nike LeBron XX',
                'category' => 'Giày Bóng Rổ',
                'brand' => 'Nike',
                'price' => 5200000,
                'sale_price' => 4290000,
                'description' => 'Giày bóng rổ Nike LeBron XX thế hệ thứ 20. Công nghệ Zoom Air toàn bộ đế giúp bật nhảy mạnh mẽ.',
                'featured' => true,
            ],
            [
                'name' => 'Adidas Predator Edge',
                'category' => 'Giày Đá Bóng',
                'brand' => 'Adidas',
                'price' => 3800000,
                'sale_price' => 2990000,
                'description' => 'Adidas Predator Edge với công nghệ Zone Skin giúp kiểm soát bóng tối đa. Đinh cao su phù hợp sân cỏ tự nhiên.',
                'featured' => false,
            ],
            [
                'name' => 'Nike Metcon 9',
                'category' => 'Giày Tập Gym',
                'brand' => 'Nike',
                'price' => 3300000,
                'sale_price' => null,
                'description' => 'Giày tập Nike Metcon 9 được thiết kế cho CrossFit và tập luyện cường độ cao. Đế phẳng ổn định cho cử tạ.',
                'featured' => false,
            ],
            [
                'name' => 'Adidas Ultraboost 23',
                'category' => 'Giày Chạy Bộ',
                'brand' => 'Adidas',
                'price' => 5000000,
                'sale_price' => 3990000,
                'description' => 'Adidas Ultraboost 23 thế hệ mới nhất. Công nghệ Linear Energy Push giúp tiết kiệm năng lượng khi chạy.',
                'featured' => true,
            ],
            [
                'name' => 'Puma Future 9',
                'category' => 'Giày Đá Bóng',
                'brand' => 'Puma',
                'price' => 3200000,
                'sale_price' => 2590000,
                'description' => 'Puma Future 9 với công nghệ FUZIONFIT ôm chân như bao tay. Đinh FG/AG phù hợp đa dạng mặt sân.',
                'featured' => false,
            ],
            [
                'name' => 'Nike Kyrie Infinity',
                'category' => 'Giày Bóng Rổ',
                'brand' => 'Nike',
                'price' => 3800000,
                'sale_price' => 2990000,
                'description' => 'Giày Kyrie Infinity thiết kế cho những pha xoay chuyển nhanh. Công nghệ Air Zoom Turbo giúp kiểm soát sân tốt hơn.',
                'featured' => false,
            ],
            [
                'name' => 'Adidas Crazyflight',
                'category' => 'Giày Năng Động',
                'brand' => 'Adidas',
                'price' => 2900000,
                'sale_price' => 2390000,
                'description' => 'Adidas Crazyflight dành cho các môn thể thao tốc độ cao. Trọng lượng siêu nhẹ chỉ 250g, bay bổng mọi sân chơi.',
                'featured' => false,
            ],
            [
                'name' => 'Converse Run Star Hike',
                'category' => 'Giày Lifestyle',
                'brand' => 'Converse',
                'price' => 2600000,
                'sale_price' => 2150000,
                'description' => 'Converse Run Star Hike phiên bản platform cá tính với đế răng cưa. Kết hợp giữa phong cách chuck và giày hiking.',
                'featured' => true,
            ],
            [
                'name' => 'Vans UltraRange Rapidweld',
                'category' => 'Giày Năng Động',
                'brand' => 'Vans',
                'price' => 2100000,
                'sale_price' => null,
                'description' => 'Vans UltraRange Rapidweld - sự kết hợp giữa thoải mái và phong cách. Đệm UltraCush cho cảm giác nhẹ nhàng.',
                'featured' => false,
            ],
            [
                'name' => 'Nike Court Vision Low',
                'category' => 'Giày Tennis',
                'brand' => 'Nike',
                'price' => 2300000,
                'sale_price' => 1890000,
                'description' => 'Nike Court Vision Low lấy cảm hứng từ giày tennis cổ điển. Thiết kế đơn giản, dễ phối đồ.',
                'featured' => false,
            ],
            [
                'name' => 'Adidas Gamecourt 2',
                'category' => 'Giày Tennis',
                'brand' => 'Adidas',
                'price' => 2000000,
                'sale_price' => 1690000,
                'description' => 'Adidas Gamecourt 2 mang đến sự ổn định cho các pha di chuyển trên sân tennis. Đế Adiwear chống trượt.',
                'featured' => false,
            ],
            [
                'name' => 'Puma IGNITE Elevate',
                'category' => 'Giày Tập Gym',
                'brand' => 'Puma',
                'price' => 2400000,
                'sale_price' => 1990000,
                'description' => 'Puma IGNITE Elevate với đệm ProFoam cho cảm giác tập luyện thoải mái. Phù hợp gym và cardio.',
                'featured' => false,
            ],
            [
                'name' => 'Nike Flex Experience 12',
                'category' => 'Giày Tập Gym',
                'brand' => 'Nike',
                'price' => 1900000,
                'sale_price' => null,
                'description' => 'Nike Flex Experience 12 có độ linh hoạt cao, phù hợp cho các bài tập đa năng. Chất liệu nhẹ, thoáng khí.',
                'featured' => false,
            ],
        ];

        foreach ($products as $data) {
            $category = $categories->firstWhere('name', $data['category']);
            $brand = $brands->firstWhere('name', $data['brand']);
            $thumbnail = $this->findImage($data['name']);

            $product = Product::create([
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . rand(1000, 9999),
                'description' => $data['description'],
                'price' => $data['price'],
                'sale_price' => $data['sale_price'],
                'thumbnail' => $thumbnail,
                'status' => true,
                'featured' => $data['featured'],
            ]);

            $createdSizes = [];
            $createdColors = [];

            // Thêm 3-5 ảnh cho sản phẩm
            for ($i = 1; $i <= 4; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'https://picsum.photos/seed/' . Str::slug($data['name']) . "-$i/400/400",
                    'sort_order' => $i,
                ]);
            }

            // Thêm sizes
            foreach ($sizes as $size) {
                $createdSizes[] = ProductSize::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'stock' => rand(5, 30),
                ]);
            }

            // Thêm màu sắc
            $colorCount = rand(2, 4);
            for ($i = 0; $i < $colorCount; $i++) {
                $createdColors[] = ProductColor::create([
                    'product_id' => $product->id,
                    'color_name' => $colors[$i]['name'],
                    'color_code' => $colors[$i]['code'],
                    'stock' => rand(5, 25),
                ]);
            }

            // Tạo variants để hệ thống có tồn kho thực tế khi thêm vào giỏ hàng
            foreach ($createdSizes as $size) {
                foreach ($createdColors as $color) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => "P{$product->id}-S{$size->id}-C{$color->id}",
                        'size' => $size->size,
                        'color_name' => $color->color_name,
                        'color_code' => $color->color_code,
                        'stock' => min($size->stock, $color->stock),
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'status' => true,
                    ]);
                }
            }
        }
    }
    private function findImage(string $productName): string
    {
        $slug = Str::slug($productName);
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($extensions as $ext) {
            $path = "products/{$slug}.{$ext}";
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }
        }

        return 'products/default.jpg';
    }
}
