<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
        $sizes = ['39', '40', '41', '42', '43', '44'];

        return view('products.index', compact('products', 'categories', 'brands', 'sizes'));
    }

    protected function buildProductQuery(Request $request)
    {
        $query = Product::active()->with(['category', 'brand'])->withAvg('reviews', 'rating');

        // Filter theo danh mục
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter theo thương hiệu
        if ($request->filled('brand')) {
            $brand = Brand::where('slug', $request->brand)->first();
            if ($brand) {
                $query->where('brand_id', $brand->id);
            }
        }

        // Filter theo giá
        if ($request->filled('min_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price)
                    ->orWhere('sale_price', '>=', $request->min_price);
            });
        }
        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price)
                    ->orWhere('sale_price', '<=', $request->max_price);
            });
        }

        // Filter theo size
        if ($request->filled('size')) {
            $query->whereHas('sizes', function ($q) use ($request) {
                $q->where('size', $request->size);
            });
        }

        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sắp xếp
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
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
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}
