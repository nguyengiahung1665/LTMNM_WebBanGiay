<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'final_price' => $this->final_price,
            'sale_percentage' => $this->sale_percentage,
            'thumbnail_url' => $this->thumbnail_url,
            'avg_rating' => $this->avg_rating,
            'featured' => (bool) $this->featured,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->image_url,
                    'sort_order' => $image->sort_order,
                ]);
            }),
            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'size' => $variant->size,
                    'color_name' => $variant->color_name,
                    'color_code' => $variant->color_code,
                    'stock' => $variant->stock,
                    'price' => $variant->price,
                    'sale_price' => $variant->sale_price,
                    'available' => $variant->status && $variant->stock > 0,
                ])->values();
            }),
            'sizes' => $this->whenLoaded('variants', function () {
                return $this->variants->where('stock', '>', 0)
                    ->groupBy('size')
                    ->map(fn ($variants, $size) => [
                        'size' => $size,
                        'stock' => $variants->sum('stock'),
                    ])->values();
            }),
            'colors' => $this->whenLoaded('variants', function () {
                return $this->variants->where('stock', '>', 0)
                    ->groupBy(fn ($variant) => $variant->color_name.'|'.$variant->color_code)
                    ->map(fn ($variants) => [
                        'color_name' => $variants->first()->color_name,
                        'color_code' => $variants->first()->color_code,
                        'stock' => $variants->sum('stock'),
                    ])->values();
            }),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
