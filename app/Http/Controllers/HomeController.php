<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // [CACHE] Trang chủ là trang có lượt truy cập nhiều nhất nhưng dữ
        // liệu (sản phẩm nổi bật/mới nhất) không cần realtime tuyệt đối
        // -> cache 30 phút, tự xoá khi admin thêm/sửa/xoá sản phẩm.
        $data = Cache::remember(CacheService::KEY_HOME, CacheService::TTL_MEDIUM, function () {
            return [
                'featuredProducts' => Product::active()->featured()->with(['category', 'brand', 'images'])->withAvg('reviews', 'rating')->latest()->take(8)->get(),
                'newProducts' => Product::active()->with(['category', 'brand', 'images'])->withAvg('reviews', 'rating')->latest()->take(8)->get(),
                'saleProducts' => Product::active()->with(['category', 'brand', 'images'])
                    ->withAvg('reviews', 'rating')->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->latest()->take(8)->get(),
                'trendingProducts' => Product::active()->with(['category', 'brand', 'images'])
                    ->withAvg('reviews', 'rating')->withCount('orderItems')->orderByDesc('order_items_count')->latest()->take(4)->get(),
                'bestSellerProducts' => Product::active()->with(['category', 'brand', 'images'])
                    ->withAvg('reviews', 'rating')->withCount('orderItems')->orderByDesc('order_items_count')->take(8)->get(),
            ];
        });

        // Danh mục & thương hiệu dùng chung cache với trang danh sách sản phẩm
        $categories = CacheService::categories();
        $brands = CacheService::brands();

        return view('home', [
            'featuredProducts' => $data['featuredProducts'],
            'newProducts' => $data['newProducts'],
            'saleProducts' => $data['saleProducts'] ?? collect(),
            'trendingProducts' => $data['trendingProducts'] ?? collect(),
            'bestSellerProducts' => $data['bestSellerProducts'] ?? collect(),
            'categories' => $categories,
            'brands' => $brands,
            'wishlistProductIds' => auth()->check()
                ? auth()->user()->wishlists()->pluck('product_id')->all()
                : [],
        ]);
    }
}
