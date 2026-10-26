<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Query param hỗ trợ: category, brand, min_price, max_price, size, color, sale, search, sort, page
     */
    public function index(Request $request)
    {
        $query = Product::active()->with(['category', 'brand', 'images'])->withAvg('reviews', 'rating');

        if ($request->filled('category')) {
            $category = Category::active()->where('slug', $request->category)->first();
            $query->where('category_id', $category?->id ?? 0);
        }

        if ($request->filled('brand')) {
            $brand = Brand::active()->where('slug', $request->brand)->first();
            $query->where('brand_id', $brand?->id ?? 0);
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [(int) $request->min_price]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [(int) $request->max_price]);
        }

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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"));
            });
        }

        switch ($request->get('sort', 'latest')) {
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

        $products = $query->paginate(12)->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * GET /api/products/{slug}
     */
    public function show($slug)
    {
        $product = Product::active()
            ->with([
                'category',
                'brand',
                'images',
                'reviews.user',
                'variants' => fn ($variants) => $variants->where('status', true),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'brand', 'images'])
            ->latest()
            ->take(4)
            ->get();

        return response()->json([
            'data' => new ProductDetailResource($product),
            'related_products' => ProductResource::collection($related),
        ]);
    }
}
