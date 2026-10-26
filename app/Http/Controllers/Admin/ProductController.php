<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\CacheService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'boolean',
            'featured' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required|string|max:100|distinct|unique:product_variants,sku',
            'variants.*.size' => 'nullable|string',
            'variants.*.color_name' => 'nullable|string',
            'variants.*.color_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.status' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . time();
        $data['status'] = $request->boolean('status', true);
        $data['featured'] = $request->boolean('featured', false);

        // Xử lý upload thumbnail
        $data['thumbnail'] = null;
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        }

        $product = Product::create($data);

        // Thêm variants (mỗi dòng = 1 tổ hợp size+màu với stock riêng)
        $this->productService->syncVariants($product, $data['variants'] ?? []);

        // Thêm images (upload file)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                if ($imageFile && $imageFile->isValid()) {
                    $path = $imageFile->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        // [CACHE] Sản phẩm mới có thể xuất hiện ở trang chủ/danh sách -> xoá cache liên quan.
        CacheService::forgetProduct($product->slug);

        return redirect('/admin/san-pham')->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit($id)
    {
        $product = Product::with(['variants', 'images'])->findOrFail($id);
        $categories = Category::active()->get();
        $brands = Brand::active()->get();
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'boolean',
            'featured' => 'boolean',
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer',
            'variants.*.sku' => 'required|string|max:100|distinct',
            'variants.*.size' => 'nullable|string',
            'variants.*.color_name' => 'nullable|string',
            'variants.*.color_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0|lte:variants.*.price',
            'variants.*.status' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:product_images,id',
        ]);

        $data['status'] = $request->boolean('status', true);
        $data['featured'] = $request->boolean('featured', false);

        // Xử lý upload thumbnail
        if ($request->hasFile('thumbnail')) {
            // Xóa ảnh cũ nếu có
            if ($product->thumbnail && !filter_var($product->thumbnail, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($product->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('products', 'public');
        } else {
            unset($data['thumbnail']);
        }

        // Chỉ update các field thuộc bảng products, bỏ variants/images ra
        $productData = collect($data)->except(['variants', 'images', 'delete_images'])->all();
        $product->update($productData);

        // Đồng bộ lại variants: xoá hết rồi tạo lại theo dữ liệu mới gửi lên
        if ($request->has('variants')) {
            $this->productService->syncVariants($product, $data['variants']);
        }

        // Xoá các ảnh gallery được chọn xoá (nếu form gửi lên)
        if ($request->filled('delete_images')) {
            $imagesToDelete = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $request->delete_images)
                ->get();
            foreach ($imagesToDelete as $image) {
                if ($image->image && !filter_var($image->image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }
        }

        // Thêm ảnh gallery mới (nếu có upload thêm)
        if ($request->hasFile('images')) {
            $currentMax = (int) $product->images()->max('sort_order');
            foreach ($request->file('images') as $index => $imageFile) {
                if ($imageFile && $imageFile->isValid()) {
                    $path = $imageFile->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'sort_order' => $currentMax + $index + 1,
                    ]);
                }
            }
        }

        // [CACHE] Xoá cache chi tiết sản phẩm này + cache trang chủ/danh sách
        // vì giá/tên/trạng thái/size/màu có thể vừa đổi.
        CacheService::forgetProduct($product->slug);

        return redirect('/admin/san-pham')->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Xóa file ảnh thumbnail
        if ($product->thumbnail && !filter_var($product->thumbnail, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        // Xóa file ảnh gallery
        foreach ($product->images as $image) {
            if ($image->image && !filter_var($image->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($image->image);
            }
        }

        $slug = $product->slug;
        $this->productService->deactivate($product);

        CacheService::forgetProduct($slug);

        return back()->with('success', 'Xóa sản phẩm thành công!');
    }
}
