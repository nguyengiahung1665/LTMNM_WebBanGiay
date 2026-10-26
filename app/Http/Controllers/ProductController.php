<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // [CACHE] Danh sách sản phẩm được lọc/sắp xếp theo query string.
        // Cache 5 phút theo hash của query string để tránh query lại DB
        // với những bộ lọc/trang phổ biến (VD: mặc định, bán chạy...).
        $cacheKey = CacheService::productListKey($request->getQueryString() ?? 'default');

        $products = Cache::remember($cacheKey, CacheService::TTL_SHORT, function () use ($request) {
            return $this->buildProductQuery($request)->paginate(12)->withQueryString();
        });

        // [CACHE] Danh mục & thương hiệu dùng cho bộ lọc - ít thay đổi
        // nên cache lâu hơn (1 giờ), tự xoá khi admin CRUD danh mục/thương hiệu.
        $categories = CacheService::categories();
        $brands = CacheService::brands();
        $sizes = ProductVariant::query()->sellable()->whereHas('product', fn ($product) => $product->active())->whereNotNull('size')
            ->where('size', '!=', '')->distinct()->orderBy('size')->pluck('size');
        $colors = ProductVariant::query()->sellable()->whereHas('product', fn ($product) => $product->active())->whereNotNull('color_name')
            ->where('color_name', '!=', '')->selectRaw('color_name, MAX(color_code) as color_code')
            ->groupBy('color_name')->orderBy('color_name')->get();
        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlists()->pluck('product_id')->all()
            : [];

        return view('products.index', compact('products', 'categories', 'brands', 'sizes', 'colors', 'wishlistProductIds'));
    }

    protected function buildProductQuery(Request $request)
    {
        $query = Product::active()->with(['category', 'brand', 'images'])->withAvg('reviews', 'rating');

        // Filter theo danh mục
        if ($request->filled('category')) {
            $category = Category::active()->where('slug', $request->category)->first();
            $query->where('category_id', $category?->id ?? 0);
        }

        // Filter theo thương hiệu
        if ($request->filled('brand')) {
            $brand = Brand::active()->where('slug', $request->brand)->first();
            $query->where('brand_id', $brand?->id ?? 0);
        }

        // Filter theo giá
        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [(int) $request->min_price]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [(int) $request->max_price]);
        }

        // Size và màu phải cùng tồn tại trên một variant đang bán, còn hàng.
        if ($request->filled('size') || $request->filled('color')) {
            $query->whereHas('variants', function ($variant) use ($request) {
                $variant->sellable()
                    ->when($request->filled('size'), fn ($q) => $q->where('size', $request->string('size')->toString()))
                    ->when($request->filled('color'), fn ($q) => $q->where('color_name', $request->string('color')->toString()));
            });
        }

        if ($request->boolean('sale')) {
            $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"));
            });
        }

        // Sắp xếp
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) asc');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'best_selling':
                $query->withCount('orderItems')->orderByDesc('order_items_count')->latest();
                break;
            default:
                $query->latest();
                break;
        }

        return $query;
    }

    public function show($slug)
    {
        // [CACHE] Trang chi tiết sản phẩm là nơi tốn nhiều truy vấn nhất
        // (join category, brand, images, sizes, colors, reviews.user) và
        // được nhiều khách xem cùng lúc -> cache 30 phút theo slug.
        // Cache tự bị xoá (Cache::forget) khi admin sửa/xoá sản phẩm này.
        $cacheKey = CacheService::productDetailKey($slug);

        $product = Cache::remember($cacheKey, CacheService::TTL_MEDIUM, function () use ($slug) {
            return Product::active()
                ->with([
                    'category',
                    'brand',
                    'images',
                    'reviews.user',
                    'variants' => fn ($query) => $query->where('status', true),
                ])
                ->withAvg('reviews', 'rating')
                ->where('slug', $slug)
                ->firstOrFail();
        });

        $relatedProducts = Product::active()
            ->with(['category', 'brand', 'images'])
            ->withAvg('reviews', 'rating')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        $wishlistProductIds = auth()->check()
            ? auth()->user()->wishlists()->pluck('product_id')->all()
            : [];

        return view('products.show', compact('product', 'relatedProducts', 'wishlistProductIds'));
    }
}
