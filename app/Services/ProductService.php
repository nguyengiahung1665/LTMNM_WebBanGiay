<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /** @param array<int, array<string, mixed>> $variants */
    public function syncVariants(Product $product, array $variants): void
    {
        $retainedIds = [];

        foreach ($variants as $index => $attributes) {
            $variantId = Arr::get($attributes, 'id');
            $variant = $variantId
                ? $product->variants()->withTrashed()->find($variantId)
                : new ProductVariant(['product_id' => $product->id]);

            if (! $variant) {
                throw ValidationException::withMessages([
                    "variants.$index.id" => ['Biến thể không thuộc sản phẩm này.'],
                ]);
            }

            $skuExists = ProductVariant::withTrashed()
                ->where('sku', $attributes['sku'])
                ->when($variant->exists, fn ($query) => $query->whereKeyNot($variant->id))
                ->exists();

            if ($skuExists) {
                throw ValidationException::withMessages([
                    "variants.$index.sku" => ['SKU này đã được dùng bởi một biến thể khác.'],
                ]);
            }

            $variant->fill([
                'sku' => $attributes['sku'],
                'size' => $attributes['size'] ?? null,
                'color_name' => $attributes['color_name'] ?? null,
                'color_code' => $attributes['color_code'] ?? null,
                'stock' => $attributes['stock'],
                'price' => $attributes['price'],
                'sale_price' => $attributes['sale_price'] ?? null,
                'status' => (bool) ($attributes['status'] ?? true),
            ]);
            $variant->restore();
            $variant->save();
            $retainedIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $retainedIds)->update(['status' => false]);
    }

    public function deactivate(Product $product): void
    {
        $product->variants()->update(['status' => false]);
        $product->update(['status' => false]);
        $product->delete();
    }
}
