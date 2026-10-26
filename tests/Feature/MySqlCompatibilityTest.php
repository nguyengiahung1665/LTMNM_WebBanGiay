<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MySqlCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_suite_runs_on_mysql_with_database_driver_tables(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        foreach (['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected MySQL table [{$table}] to exist.");
        }
    }

    public function test_customer_can_browse_products_and_add_a_variant_to_the_cart(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Giày MySQL', 'slug' => 'giay-mysql', 'status' => true]);
        $brand = Brand::create(['name' => 'MySQL Brand', 'slug' => 'mysql-brand', 'status' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Giày kiểm thử MySQL',
            'slug' => 'giay-kiem-thu-mysql',
            'price' => 750000,
            'status' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'MYSQL-42-BLACK',
            'size' => '42',
            'color_name' => 'Đen',
            'stock' => 10,
            'price' => 750000,
            'status' => true,
        ]);

        $this->get('/san-pham')->assertOk()->assertSee($product->name);
        $this->get('/san-pham/'.$product->slug)->assertOk()->assertSee($product->name);

        $this->actingAs($user)->post('/gio-hang/them', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect('/gio-hang');

        $this->get('/gio-hang')->assertOk()->assertSee($product->name);
        $item = CartItem::where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->post('/gio-hang/cap-nhat/'.$item->id, ['quantity' => 3])->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 3]);

        $this->post('/gio-hang/xoa/'.$item->id)->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_sanctum_token_authenticates_an_api_request_on_mysql(): void
    {
        $user = User::factory()->create([
            'email' => 'sanctum-mysql@example.com',
            'password' => '12345678',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '12345678',
        ])->assertOk()->assertJsonStructure(['token']);

        $this->withToken($login->json('token'))
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $category = Category::create(['name' => 'API MySQL', 'slug' => 'api-mysql', 'status' => true]);
        $brand = Brand::create(['name' => 'API Brand', 'slug' => 'api-brand', 'status' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Giày API MySQL',
            'slug' => 'giay-api-mysql',
            'price' => 900000,
            'status' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'API-MYSQL-41',
            'size' => '41',
            'color_name' => 'Trắng',
            'stock' => 5,
            'price' => 900000,
            'status' => true,
        ]);

        $this->getJson('/api/products?search=API+MySQL')
            ->assertOk()
            ->assertJsonPath('data.0.name', $product->name);
        $this->getJson('/api/products/'.$product->slug)
            ->assertOk()
            ->assertJsonPath('data.name', $product->name);

        $token = $login->json('token');
        $cart = $this->withToken($token)->postJson('/api/cart', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertCreated();

        $this->withToken($token)->putJson('/api/cart/'.$cart->json('data.id'), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('data.quantity', 2);

        $order = $this->withToken($token)->postJson('/api/orders', [
            'full_name' => 'Nguyễn Văn API',
            'email' => $user->email,
            'phone' => '0912345678',
            'address' => '123 Nguyễn Trãi, TP Hồ Chí Minh',
        ])->assertCreated();

        $this->withToken($token)->getJson('/api/orders')->assertOk();
        $this->withToken($token)->getJson('/api/orders/'.$order->json('data.id'))->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertDatabaseHas('orders', ['id' => $order->json('data.id'), 'user_id' => $user->id]);
        $this->assertSame(3, $variant->fresh()->stock);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/don-hang/'.$order->json('data.id').'/cap-nhat', [
            'status' => 'processing',
        ])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->json('data.id'), 'status' => 'processing']);

        $this->actingAs($user)->post('/yeu-thich/toggle/'.$product->id)->assertRedirect();
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)->post('/san-pham/'.$product->slug.'/danh-gia', [
            'rating' => 5,
            'comment' => 'Hoạt động tốt trên MySQL.',
        ])->assertRedirect();
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'product_id' => $product->id, 'rating' => 5]);
    }

    public function test_user_can_register_on_mysql(): void
    {
        $this->post('/dang-ky', [
            'name' => 'Khách MySQL',
            'email' => 'register-mysql@example.com',
            'phone' => '0912345678',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'register-mysql@example.com']);
    }

    public function test_admin_can_create_update_and_deactivate_a_product_with_variants_on_mysql(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/danh-muc', [
            'name' => 'Danh mục quản trị MySQL',
            'status' => true,
        ])->assertRedirect('/admin/danh-muc');
        $category = Category::where('slug', 'danh-muc-quan-tri-mysql')->firstOrFail();

        $this->actingAs($admin)->post('/admin/thuong-hieu', [
            'name' => 'Thương hiệu quản trị MySQL',
            'status' => true,
        ])->assertRedirect('/admin/thuong-hieu');
        $brand = Brand::where('slug', 'thuong-hieu-quan-tri-mysql')->firstOrFail();

        $this->actingAs($admin)->post('/admin/san-pham', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Sản phẩm quản trị MySQL',
            'price' => 1000000,
            'status' => true,
            'variants' => [[
                'sku' => 'ADMIN-MYSQL-40',
                'size' => '40',
                'color_name' => 'Đen',
                'stock' => 8,
                'price' => 1000000,
                'status' => true,
            ]],
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/san-pham');

        $product = Product::where('name', 'Sản phẩm quản trị MySQL')->firstOrFail();
        $variant = $product->variants()->firstOrFail();
        $this->actingAs($admin)->get('/admin/san-pham')->assertOk()->assertSee($product->name);

        $this->actingAs($admin)->post('/admin/san-pham/'.$product->id.'/sua', [
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Sản phẩm quản trị MySQL cập nhật',
            'price' => 950000,
            'status' => true,
            'featured' => true,
            'variants' => [[
                'id' => $variant->id,
                'sku' => $variant->sku,
                'size' => '40',
                'color_name' => 'Đen',
                'stock' => 6,
                'price' => 950000,
                'status' => true,
            ]],
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/san-pham');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Sản phẩm quản trị MySQL cập nhật',
            'price' => 950000,
        ]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 6, 'price' => 950000]);

        $this->actingAs($admin)->post('/admin/san-pham/'.$product->id.'/xoa')->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'status' => false]);

        $this->actingAs($admin)->post('/admin/danh-muc/'.$category->id.'/sua', [
            'name' => 'Danh mục quản trị MySQL cập nhật',
            'status' => true,
        ])->assertRedirect('/admin/danh-muc');
        $this->actingAs($admin)->post('/admin/thuong-hieu/'.$brand->id.'/sua', [
            'name' => 'Thương hiệu quản trị MySQL cập nhật',
            'status' => true,
        ])->assertRedirect('/admin/thuong-hieu');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'danh-muc-quan-tri-mysql-cap-nhat']);
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'slug' => 'thuong-hieu-quan-tri-mysql-cap-nhat']);

        $this->actingAs($admin)->post('/admin/danh-muc/'.$category->id.'/xoa')->assertRedirect();
        $this->actingAs($admin)->post('/admin/thuong-hieu/'.$brand->id.'/xoa')->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}
