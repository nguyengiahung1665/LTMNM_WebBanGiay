<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'size',
        'color_name',
        'color_code',
        'stock',
        'price',
        'sale_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price' => 'integer',
            'sale_price' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_variant_id');
    }

    // Nhãn hiển thị gộp, vd: "Size 39 - Đen"
    public function getLabelAttribute(): string
    {
        $parts = [];
        if ($this->size) {
            $parts[] = 'Size ' . $this->size;
        }
        if ($this->color_name) {
            $parts[] = $this->color_name;
        }
        return implode(' - ', $parts) ?: '—';
    }

    public function getFinalPriceAttribute(): int
    {
        return $this->sale_price ?? $this->price;
    }

    public function scopeSellable($query)
    {
        return $query->where('status', true)->where('stock', '>', 0);
    }
}
