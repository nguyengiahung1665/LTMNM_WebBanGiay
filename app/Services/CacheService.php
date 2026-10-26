<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Tập trung quản lý cache key & TTL cho toàn hệ thống.
 *
 * Mục tiêu: mọi nơi cần cache/xoá cache đều gọi qua đây,
 * tránh việc rải rác chuỗi key ở nhiều Controller khác nhau
 * (dễ gây "quên xoá cache" khi cập nhật dữ liệu).
 */
class CacheService
{
    // Thời gian sống (giây)
    public const TTL_SHORT = 300;      // 5 phút  - danh sách/kết quả lọc sản phẩm
    public const TTL_MEDIUM = 1800;    // 30 phút - trang chủ, chi tiết sản phẩm
    public const TTL_LONG = 3600;      // 1 giờ   - danh mục, thương hiệu (ít thay đổi)

    public const KEY_CATEGORIES = 'categories.active';
    public const KEY_BRANDS = 'brands.active';
    public const KEY_HOME = 'home.data';
    public const KEY_PRODUCTS_VERSION = 'products.cache.version';

    public static function categories(): mixed
    {
        return Cache::remember(self::KEY_CATEGORIES, self::TTL_LONG, function () {
            return \App\Models\Category::active()
                ->whereHas('products', fn ($products) => $products
                    ->active()
                    ->whereHas('variants', fn ($variants) => $variants->sellable()))
                ->orderBy('name')
                ->get();
        });
    }

    public static function brands(): mixed
    {
        return Cache::remember(self::KEY_BRANDS, self::TTL_LONG, function () {
            return \App\Models\Brand::active()
                ->whereHas('products', fn ($products) => $products
                    ->active()
                    ->whereHas('variants', fn ($variants) => $variants->sellable()))
                ->orderBy('name')
                ->get();
        });
    }

    public static function productDetailKey(string $slug): string
    {
        return "product.detail.{$slug}";
    }

    public static function productListKey(string $queryString): string
    {
        $version = (int) Cache::get(self::KEY_PRODUCTS_VERSION, 1);

        return "products.list.{$version}.".md5($queryString);
    }

    /**
     * Gọi khi danh mục thay đổi (thêm/sửa/xoá).
     */
    public static function forgetCategories(): void
    {
        Cache::forget(self::KEY_CATEGORIES);
        Cache::forget(self::KEY_HOME);
    }

    /**
     * Gọi khi thương hiệu thay đổi (thêm/sửa/xoá).
     */
    public static function forgetBrands(): void
    {
        Cache::forget(self::KEY_BRANDS);
        Cache::forget(self::KEY_HOME);
    }

    /**
     * Gọi khi 1 sản phẩm thay đổi (thêm/sửa/xoá) - xoá cache chi tiết
     * sản phẩm đó + toàn bộ cache danh sách/trang chủ (vì có thể ảnh
     * hưởng tới bộ lọc, sắp xếp, sản phẩm nổi bật...).
     */
    public static function forgetProduct(?string $slug = null): void
    {
        if ($slug) {
            Cache::forget(self::productDetailKey($slug));
        }

        Cache::forget(self::KEY_HOME);
        Cache::forget(self::KEY_CATEGORIES);
        Cache::forget(self::KEY_BRANDS);
        Cache::forever(self::KEY_PRODUCTS_VERSION, (int) Cache::get(self::KEY_PRODUCTS_VERSION, 1) + 1);

        // Tăng version để mọi tổ hợp filter/sort tự chuyển sang cache key mới.
        // Các key cũ hết hạn theo TTL ngắn, không cần scan toàn bộ cache table.
    }
}
