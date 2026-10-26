<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_options_and_combinations_use_sellable_product_variants(): void
    {
        [$runner, $street, $court] = $this->seedCatalog();
        Brand::create(['name' => 'Empty Brand', 'slug' => 'empty-brand', 'status' => true]);
        Category::create(['name' => 'Empty Category', 'slug' => 'empty-category', 'status' => true]);

        $catalog = $this->get('/san-pham')->assertOk();
        $this->assertEqualsCanonicalizing(['39', '40', '42'], $catalog->viewData('sizes')->all());
        $this->assertEqualsCanonicalizing(['Black', 'Red', 'White'], $catalog->viewData('colors')->pluck('color_name')->all());
        $this->assertNotContains('Empty Brand', $catalog->viewData('brands')->pluck('name')->all());
        $this->assertNotContains('Empty Category', $catalog->viewData('categories')->pluck('name')->all());

        $this->get('/san-pham?brand=nike')->assertOk()
            ->assertSee($runner->name)->assertSee($court->name)->assertDontSee($street->name);
        $this->get('/san-pham?category=running')->assertOk()
            ->assertSee($runner->name)->assertDontSee($court->name)->assertDontSee($street->name);
        $this->get('/san-pham?size=42')->assertOk()
            ->assertSee($runner->name)->assertSee($court->name)->assertDontSee($street->name);
        $this->get('/san-pham?color=White')->assertOk()
            ->assertSee($runner->name)->assertDontSee($court->name)->assertDontSee($street->name);

        // Runner has size 42 in White, but its Black/42 variant is sold out.
        // Both conditions must match the same in-stock variant.
        $this->get('/san-pham?brand=nike&size=42&color=Black')->assertOk()
            ->assertSee($court->name)->assertDontSee($runner->name)->assertDontSee($street->name);
        $this->get('/san-pham?brand=nike&size=42&color=White')->assertOk()
            ->assertSee($runner->name)->assertDontSee($court->name)->assertDontSee($street->name);
    }

    public function test_search_price_sale_and_sort_keep_working_together(): void
    {
        [$runner, $street, $court] = $this->seedCatalog();

        $this->get('/san-pham?search=air&brand=nike&size=42')->assertOk()
            ->assertSee($runner->name)->assertDontSee($street->name)->assertDontSee($court->name);
        $this->get('/san-pham?min_price=2400000&max_price=2600000')->assertOk()
            ->assertSee($runner->name)->assertDontSee($street->name)->assertDontSee($court->name);
        $this->get('/san-pham?sale=1')->assertOk()
            ->assertSee($runner->name)->assertDontSee($street->name)->assertDontSee($court->name);

        $this->get('/san-pham?sort=price_asc')->assertOk()
            ->assertSeeInOrder([$street->name, $runner->name, $court->name]);
        $this->get('/san-pham?sort=price_desc')->assertOk()
            ->assertSeeInOrder([$court->name, $runner->name, $street->name]);
        $this->get('/san-pham?sort=latest')->assertOk()
            ->assertSeeInOrder([$court->name, $street->name, $runner->name]);
    }

    public function test_pagination_preserves_active_filters(): void
    {
        [, $street] = $this->seedCatalog();
        $adidas = $street->brand;
        $lifestyle = $street->category;

        for ($index = 1; $index <= 12; $index++) {
            $product = $this->createProduct($adidas, $lifestyle, [
                'name' => "Adidas Pagination {$index}",
                'slug' => "adidas-pagination-{$index}",
                'price' => 2000000 + ($index * 10000),
            ]);
            $this->variant($product, "PAGE-{$index}", '40', 'Red', '#EF4444', 8);
        }

        $response = $this->get('/san-pham?brand=adidas&size=40')->assertOk();
        $products = $response->viewData('products');
        $this->assertSame(13, $products->total());

        parse_str((string) parse_url($products->nextPageUrl(), PHP_URL_QUERY), $query);
        $this->assertSame('adidas', $query['brand']);
        $this->assertSame('40', $query['size']);
        $this->assertSame('2', (string) $query['page']);
        $this->get($products->nextPageUrl())->assertOk();
    }

    public function test_api_uses_variants_and_image_hierarchy_is_safe(): void
    {
        [$runner, $street, $court] = $this->seedCatalog();

        $runner->update(['thumbnail' => 'products/does-not-exist.jpg']);
        ProductImage::create([
            'product_id' => $runner->id,
            'image' => 'products/nike-air-force-1-low.jpg',
            'sort_order' => 1,
        ]);
        $court->update(['thumbnail' => null]);

        $this->assertStringEndsWith('/storage/products/nike-air-force-1-low.jpg', $runner->fresh()->thumbnail_url);
        $this->assertStringEndsWith('/images/product-placeholder.svg', $court->fresh()->thumbnail_url);

        $this->getJson('/api/products?brand=nike&size=42&color=Black')->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $court->name);

        $detail = $this->getJson('/api/products/'.$runner->slug)->assertOk();
        $detail->assertJsonPath('data.name', $runner->name)
            ->assertJsonPath('data.thumbnail_url', $runner->fresh()->thumbnail_url)
            ->assertJsonCount(3, 'data.variants');
        $this->assertEqualsCanonicalizing(['40', '42'], collect($detail->json('data.sizes'))->pluck('size')->all());
        $this->assertEqualsCanonicalizing(['Black', 'White'], collect($detail->json('data.colors'))->pluck('color_name')->all());

        $this->get('/san-pham/'.$runner->slug)->assertOk()
            ->assertSee('data-product-image', false)
            ->assertSee('data-fallback-src', false);
        $this->assertDatabaseCount('product_sizes', 0);
        $this->assertDatabaseCount('product_colors', 0);
    }

    /** @return array{Product, Product, Product} */
    private function seedCatalog(): array
    {
        $nike = Brand::create(['name' => 'Nike', 'slug' => 'nike', 'status' => true]);
        $adidas = Brand::create(['name' => 'Adidas', 'slug' => 'adidas', 'status' => true]);
        $running = Category::create(['name' => 'Running', 'slug' => 'running', 'status' => true]);
        $lifestyle = Category::create(['name' => 'Lifestyle', 'slug' => 'lifestyle', 'status' => true]);
        $basketball = Category::create(['name' => 'Basketball', 'slug' => 'basketball', 'status' => true]);

        $runner = $this->createProduct($nike, $running, [
            'name' => 'Nike Air Runner Pro',
            'slug' => 'nike-air-runner-pro',
            'price' => 3000000,
            'sale_price' => 2500000,
            'thumbnail' => 'products/nike-air-zoom-pegasus-40.jpg',
        ], now()->subDays(2));
        $this->variant($runner, 'RUN-40-BLK', '40', 'Black', '#000000', 5);
        $this->variant($runner, 'RUN-42-BLK', '42', 'Black', '#000000', 0);
        $this->variant($runner, 'RUN-42-WHT', '42', 'White', '#FFFFFF', 7);

        $street = $this->createProduct($adidas, $lifestyle, [
            'name' => 'Adidas Street Air Classic',
            'slug' => 'adidas-street-air-classic',
            'price' => 2000000,
            'thumbnail' => 'products/adidas-samba-og.jpg',
        ], now()->subDay());
        $this->variant($street, 'STREET-39-RED', '39', 'Red', '#EF4444', 5);
        $this->variant($street, 'STREET-40-RED', '40', 'Red', '#EF4444', 4);
        $this->variant($street, 'STREET-45-GRN', '45', 'Green', '#16A34A', 0);

        $court = $this->createProduct($nike, $basketball, [
            'name' => 'Nike Court Force',
            'slug' => 'nike-court-force',
            'price' => 4000000,
            'thumbnail' => 'products/nike-lebron-xx.jpg',
        ], now());
        $this->variant($court, 'COURT-42-BLK', '42', 'Black', '#000000', 8);

        return [$runner, $street, $court];
    }

    private function createProduct(Brand $brand, Category $category, array $attributes, $createdAt = null): Product
    {
        $product = Product::create(array_merge([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'description' => 'Dữ liệu kiểm thử filter.',
            'status' => true,
            'featured' => false,
        ], $attributes));

        if ($createdAt) {
            $product->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        return $product;
    }

    private function variant(Product $product, string $sku, string $size, string $color, string $colorCode, int $stock): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'size' => $size,
            'color_name' => $color,
            'color_code' => $colorCode,
            'stock' => $stock,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'status' => true,
        ]);
    }
}
