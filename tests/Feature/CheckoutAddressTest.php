<?php

namespace Tests\Feature;

use App\Events\NewOrderPlaced;
use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_structured_address_fields_and_prefills_profile_address(): void
    {
        [$province, $ward] = $this->locationPair();
        [$user] = $this->createCart($province, $ward);

        $response = $this->actingAs($user)->get('/dat-hang')->assertOk();

        $response
            ->assertSee('name="province_code"', false)
            ->assertSee('name="ward_code"', false)
            ->assertSee('name="address_detail"', false)
            ->assertSee('data-selected="'.$province['code'].'"', false)
            ->assertSee('data-selected="'.$ward['code'].'"', false)
            ->assertSee('value="12 Nguyễn Trãi"', false)
            ->assertDontSee('name="address"', false);
    }

    public function test_checkout_requires_each_structured_address_field(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from('/dat-hang')->post('/dat-hang', [
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '0912345678',
        ])->assertRedirect('/dat-hang')->assertSessionHasErrors([
            'province_code',
            'ward_code',
            'address_detail',
        ]);
    }

    public function test_checkout_rejects_ward_outside_selected_province(): void
    {
        $locations = $this->locations();
        $user = User::factory()->create();

        $this->actingAs($user)->from('/dat-hang')->post('/dat-hang', [
            'full_name' => $user->name,
            'email' => $user->email,
            'phone' => '0912345678',
            'province_code' => $locations[0]['code'],
            'ward_code' => $locations[1]['wards'][0]['code'],
            'address_detail' => '12 Nguyễn Trãi',
        ])->assertRedirect('/dat-hang')->assertSessionHasErrors('ward_code');
    }

    public function test_checkout_stores_full_address_and_keeps_inventory_flow(): void
    {
        Mail::fake();
        Event::fake([NewOrderPlaced::class]);

        [$province, $ward] = $this->locationPair();
        [$user, $variant] = $this->createCart($province, $ward);

        $response = $this->actingAs($user)->post('/dat-hang', [
            'full_name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'phone' => '0912345678',
            'province_code' => $province['code'],
            'ward_code' => $ward['code'],
            'address_detail' => '123 Nguyễn Văn A',
            'note' => 'Giao giờ hành chính',
        ]);

        $order = $user->orders()->latest('id')->firstOrFail();

        $response->assertRedirect('/dat-hang/thanh-cong/'.$order->id);
        $this->assertSame(
            '123 Nguyễn Văn A, '.$ward['name'].', '.$province['name'],
            $order->address
        );
        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    private function createCart(array $province, array $ward): array
    {
        $user = User::factory()->create([
            'phone' => '0912345678',
            'province_code' => $province['code'],
            'province_name' => $province['name'],
            'ward_code' => $ward['code'],
            'ward_name' => $ward['name'],
            'address_detail' => '12 Nguyễn Trãi',
        ]);
        $category = Category::create(['name' => 'Giày chạy', 'slug' => 'giay-chay-test', 'status' => true]);
        $brand = Brand::create(['name' => 'Nike', 'slug' => 'nike-test', 'status' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Giày kiểm thử checkout',
            'slug' => 'giay-kiem-thu-checkout',
            'price' => 600000,
            'status' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CHECKOUT-42-BLACK',
            'size' => '42',
            'color_name' => 'Đen',
            'stock' => 5,
            'price' => 600000,
            'status' => true,
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        return [$user, $variant];
    }

    private function locationPair(): array
    {
        $province = $this->locations()[0];

        return [$province, $province['wards'][0]];
    }

    private function locations(): array
    {
        return json_decode(
            (string) file_get_contents(resource_path('data/vietnam-addresses.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
