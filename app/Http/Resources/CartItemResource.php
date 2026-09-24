<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'product' => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'slug' => $this->product?->slug,
                'thumbnail_url' => $this->product?->thumbnail_url,
                'final_price' => $this->product?->final_price,
            ],
            'product_variant' => $this->variant ? [
                'id' => $this->variant->id,
                'sku' => $this->variant->sku,
                'size' => $this->variant->size,
                'color_name' => $this->variant->color_name,
                'color_code' => $this->variant->color_code,
                'stock' => $this->variant->stock,
                'final_price' => $this->variant->final_price,
            ] : null,
        ];
    }
}
