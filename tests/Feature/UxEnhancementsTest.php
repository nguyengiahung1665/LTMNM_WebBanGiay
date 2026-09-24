<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UxEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_structured_profile_address(): void
    {
        $user = User::factory()->create();
        $locations = json_decode(file_get_contents(resource_path('data/vietnam-addresses.json')), true);
        $province = $locations[0];
        $ward = $province['wards'][0];

        $this->actingAs($user)->get('/thong-tin')
            ->assertOk()
            ->assertSee('id="vietnam-addresses-data"', false)
            ->assertSee('Cập nhật thông tin cá nhân');

        $this->post('/thong-tin', [
            'name' => 'Nguyễn Văn An',
            'phone' => '0912345678',
            'gender' => 'male',
            'province_code' => $province['code'],
            'ward_code' => $ward['code'],
            'address_detail' => '12 Nguyễn Trãi',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'province_name' => $province['name'],
            'ward_name' => $ward['name'],
            'gender' => 'male',
        ]);
    }

    public function test_profile_rejects_ward_outside_selected_province(): void
    {
        $user = User::factory()->create();
        $locations = json_decode(file_get_contents(resource_path('data/vietnam-addresses.json')), true);

        $this->actingAs($user)->from('/thong-tin')->post('/thong-tin', [
            'name' => 'Nguyễn Văn An', 'phone' => '0912345678', 'gender' => 'other',
            'province_code' => $locations[0]['code'], 'ward_code' => $locations[1]['wards'][0]['code'],
            'address_detail' => '12 Nguyễn Trãi',
        ])->assertRedirect('/thong-tin')->assertSessionHasErrors('ward_code');
    }

    public function test_product_detail_has_editable_stock_bound_quantity_and_variant_controls(): void
    {
        $category = Category::create(['name' => 'Giày chạy', 'slug' => 'giay-chay', 'status' => true]);
        $brand = Brand::create(['name' => 'Nike', 'slug' => 'nike', 'status' => true]);
        $product = Product::create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Giày test', 'slug' => 'giay-test', 'price' => 1000000, 'status' => true]);
        ProductVariant::create(['product_id' => $product->id, 'sku' => 'TEST-42', 'size' => '42', 'color_name' => 'Đen', 'stock' => 3, 'price' => 1000000, 'status' => true]);

        $response = $this->get('/san-pham/giay-test')->assertOk();
        $response->assertSee('id="qty-minus"', false)->assertSee('inputmode="numeric"', false)->assertSee('class="variant-option"', false);
        $this->assertStringNotContainsString('id="qty" value="1" min="1" max="99" readonly', $response->getContent());
    }

    public function test_admin_order_status_requires_explicit_modal_confirmation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::create(['user_id' => $admin->id, 'order_code' => 'DH-UX-1', 'full_name' => 'Khách hàng', 'email' => 'customer@example.com', 'phone' => '0912345678', 'address' => 'Hà Nội', 'shipping_fee' => 0, 'total_price' => 100000, 'status' => 'pending', 'payment_method' => 'cod']);

        $response = $this->actingAs($admin)->get('/admin/don-hang/'.$order->id)->assertOk();
        $response->assertSee('Cập nhật trạng thái')->assertSee('id="status-modal"', false);
        $this->assertStringNotContainsString('onchange="this.form.submit()"', $response->getContent());
    }
}
