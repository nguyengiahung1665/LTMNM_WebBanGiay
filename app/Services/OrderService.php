<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /** @param array<string, mixed> $shipping */
    public function createFromCart(User $user, array $shipping): Order
    {
        return DB::transaction(function () use ($user, $shipping): Order {
            $items = CartItem::with('product')->where('user_id', $user->id)->lockForUpdate()->get();
            if ($items->isEmpty()) throw ValidationException::withMessages(['cart' => ['Giỏ hàng trống.']]);
            if ($items->contains(fn (CartItem $item) => ! $item->product_variant_id)) throw ValidationException::withMessages(['cart' => ['Giỏ hàng có sản phẩm chưa chọn biến thể.']]);

            $variants = ProductVariant::whereIn('id', $items->pluck('product_variant_id')->unique())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $subtotal = 0;
            foreach ($items as $item) {
                $variant = $variants->get($item->product_variant_id);
                if (! $variant || ! $variant->status || $variant->product_id !== $item->product_id || $item->quantity > $variant->stock) {
                    throw ValidationException::withMessages(['cart' => ["Biến thể của {$item->product->name} không còn đủ hàng."]]);
                }
                $subtotal += $variant->final_price * $item->quantity;
            }
            $shippingFee = $subtotal >= 500000 ? 0 : 30000;
            $order = Order::create(array_merge($shipping, ['user_id' => $user->id, 'order_code' => 'DH'.now()->format('Ymd').strtoupper(Str::random(8)), 'shipping_fee' => $shippingFee, 'total_price' => $subtotal + $shippingFee, 'status' => 'pending', 'payment_method' => 'cod']));
            foreach ($items as $item) {
                $variant = $variants[$item->product_variant_id];
                if (ProductVariant::whereKey($variant->id)->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity) !== 1) {
                    throw ValidationException::withMessages(['cart' => ["Biến thể {$variant->sku} vừa hết hàng."]]);
                }
                OrderItem::create(['order_id' => $order->id, 'product_id' => $item->product_id, 'product_variant_id' => $variant->id, 'variant_sku' => $variant->sku, 'variant_size' => $variant->size, 'variant_color_name' => $variant->color_name, 'variant_color_code' => $variant->color_code, 'product_name' => $item->product->name, 'product_price' => $variant->final_price, 'quantity' => $item->quantity]);
            }
            CartItem::where('user_id', $user->id)->delete();
            return $order->load('items');
        }, 3);
    }
}
