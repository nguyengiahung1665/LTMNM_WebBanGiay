<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function placeholderUrl(): string
    {
        return asset('images/product-placeholder.svg');
    }

    public static function resolveUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $path = str_replace('\\', '/', ltrim($value, '/'));
        $path = Str::startsWith($path, 'public/') ? Str::after($path, 'public/') : $path;

        if (Str::startsWith($path, 'storage/')) {
            $storagePath = Str::after($path, 'storage/');

            return Storage::disk('public')->exists($storagePath)
                ? asset('storage/'.$storagePath)
                : null;
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return Storage::disk('public')->exists($path)
            ? asset('storage/'.$path)
            : null;
    }

    public function getImageUrlAttribute(): string
    {
        return self::resolveUrl($this->image) ?? self::placeholderUrl();
    }
}
