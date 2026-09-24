<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function add(User $user, int $productId, int $variantId, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => ['Số lượng phải lớn hơn 0.']]);
        }

        return DB::transaction(function () use ($user, $productId, $variantId, $quantity): CartItem {
            $product = Product::active()->find($productId);
            if (! $product) {
                throw ValidationException::withMessages(['product_id' => ['Sản phẩm không tồn tại hoặc đã ngừng bán.']]);
            }

            $variant = ProductVariant::where('product_id', $product->id)->lockForUpdate()->find($variantId);
            $this->assertSellableVariant($variant, 'product_variant_id');

            $item = CartItem::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            $newQuantity = ($item?->quantity ?? 0) + $quantity;
            if ($newQuantity > $variant->stock) {
                throw ValidationException::withMessages(['quantity' => ['Số lượng trong giỏ vượt quá tồn kho hiện tại.']]);
            }

            if ($item) {
                $item->update(['quantity' => $newQuantity]);
            } else {
                $item = CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }

            return $item->load(['product', 'variant']);
        });
    }

    public function update(User $user, int $itemId, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => ['Số lượng phải lớn hơn 0.']]);
        }

        return DB::transaction(function () use ($user, $itemId, $quantity): CartItem {
            $item = CartItem::where('user_id', $user->id)->lockForUpdate()->find($itemId);
            if (! $item) {
                throw ValidationException::withMessages(['cart_item' => ['Không tìm thấy mục giỏ hàng của bạn.']]);
            }

            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
            $this->assertSellableVariant($variant, 'product_variant_id');

            if ($quantity > $variant->stock) {
                throw ValidationException::withMessages(['quantity' => ['Số lượng trong giỏ vượt quá tồn kho hiện tại.']]);
            }

            $item->update(['quantity' => $quantity]);
            return $item->load(['product', 'variant']);
        });
    }

    private function assertSellableVariant(?ProductVariant $variant, string $field): void
    {
        if (! $variant || ! $variant->status) {
            throw ValidationException::withMessages([$field => ['Biến thể không tồn tại hoặc không còn bán.']]);
        }
        if ($variant->stock <= 0) {
            throw ValidationException::withMessages([$field => ['Biến thể đã hết hàng.']]);
        }
    }
}
