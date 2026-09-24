@extends('admin.layouts.admin')

@section('title', 'Sửa sản phẩm')

@section('content')
    <div class="page-header">
        <h2>✏️ Sửa sản phẩm</h2>
        <a href="/admin/san-pham" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="card">
        <form method="POST" action="/admin/san-pham/{{ $product->id }}/sua" enctype="multipart/form-data" id="product-edit-form" data-unsaved-warning>
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Tên sản phẩm *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Danh mục *</label>
                        <select name="category_id" class="form-control" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Thương hiệu *</label>
                        <select name="brand_id" class="form-control" required>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" class="form-control"
                    rows="4">{{ old('description', $product->description) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Giá gốc *</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}"
                        required min="0">
                </div>
                <div class="form-group">
                    <label>Giá khuyến mãi</label>
                    <input type="number" name="sale_price" class="form-control"
                        value="{{ old('sale_price', $product->sale_price) }}" min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện</label>
                @if($product->thumbnail_url)
                    <div style="margin-bottom: 8px;">
                        <img src="{{ $product->thumbnail_url }}" alt="Current thumbnail"
                            style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #e0e0e0;">
                    </div>
                @endif
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                <small style="color: #999;">Chọn file ảnh mới nếu muốn thay đổi (JPEG, PNG, JPG, GIF, WebP, tối đa
                    2MB).</small>
                @error('thumbnail') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="status" value="1" {{ $product->status ? 'checked' : '' }}> Hiển thị
                </label>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="featured" value="1" {{ $product->featured ? 'checked' : '' }}> Nổi bật
                </label>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-bottom: 16px;">📏🎨 Biến thể (Size + Màu + Tồn kho)</h4>
            <p style="font-size: 13px; color: #999; margin-bottom: 8px;">
                Mỗi dòng là 1 tổ hợp size + màu cụ thể với số lượng tồn kho riêng của tổ hợp đó.
                Có thể để trống Size hoặc Màu nếu sản phẩm không phân loại theo chiều đó.
            </p>
            <div id="variants-wrapper">
                @forelse($product->variants as $i => $variant)
                    <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                        <input type="text" name="variants[{{ $i }}][sku]" class="form-control" placeholder="SKU"
                            style="max-width: 150px;" value="{{ $variant->sku }}" required>
                        <input type="text" name="variants[{{ $i }}][size]" class="form-control" placeholder="Size (VD: 39)"
                            style="max-width: 130px;" value="{{ $variant->size }}">
                        <input type="text" name="variants[{{ $i }}][color_name]" class="form-control" placeholder="Tên màu (VD: Đen)"
                            style="max-width: 150px;" value="{{ $variant->color_name }}">
                        <input type="number" name="variants[{{ $i }}][stock]" class="form-control" placeholder="Số lượng"
                            style="max-width: 120px;" min="0" value="{{ $variant->stock }}" required>
                        <input type="number" name="variants[{{ $i }}][price]" class="form-control" placeholder="Giá"
                            style="max-width: 140px;" min="0" value="{{ $variant->price }}" required>
                        <input type="number" name="variants[{{ $i }}][sale_price]" class="form-control" placeholder="Giá sale"
                            style="max-width: 140px;" min="0" value="{{ $variant->sale_price }}">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="this.parentElement.remove()">Xóa</button>
                    </div>
                @empty
                    <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                        <input type="text" name="variants[0][sku]" class="form-control" placeholder="SKU" style="max-width: 150px;" required>
                        <input type="text" name="variants[0][size]" class="form-control" placeholder="Size (VD: 39)" style="max-width: 130px;">
                        <input type="text" name="variants[0][color_name]" class="form-control" placeholder="Tên màu (VD: Đen)" style="max-width: 150px;">
                        <input type="number" name="variants[0][stock]" class="form-control" placeholder="Số lượng" style="max-width: 120px;" min="0" required>
                        <input type="number" name="variants[0][price]" class="form-control" placeholder="Giá" style="max-width: 140px;" min="0" required>
                        <input type="number" name="variants[0][sale_price]" class="form-control" placeholder="Giá sale" style="max-width: 140px;" min="0">
                        <button type="button" class="btn btn-sm btn-secondary" onclick="this.parentElement.remove()">Xóa</button>
                    </div>
                @endforelse
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addVariant()">+ Thêm biến thể</button>
            <p style="font-size: 13px; color: #999; margin-top: 6px;">Xóa hết dòng (để trống) và bấm "Cập nhật" sẽ xóa toàn bộ biến thể của sản phẩm.</p>

            <hr style="margin: 20px 0;">

            <h4 style="margin-bottom: 16px;">🖼️ Thêm ảnh gallery (upload nhiều file)</h4>
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple style="padding: 8px;">
            <small style="color: #999;">Ảnh mới sẽ được thêm vào, không xóa ảnh cũ (tối đa 2MB mỗi file).</small>
            @error('images.*') <small style="color: red;">{{ $message }}</small> @enderror

            @if($product->images->count())
                <h5 style="margin: 16px 0 8px;">Ảnh hiện tại (tick để xóa)</h5>
                <div style="display: flex; flex-wrap: wrap; gap: 12px;">
                    @foreach($product->images as $image)
                        <label style="text-align: center; cursor: pointer;">
                            <img src="{{ $image->image_url ?? asset('storage/' . $image->image) }}"
                                style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e0e0e0; display: block; margin-bottom: 4px;">
                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"> Xóa
                        </label>
                    @endforeach
                </div>
            @endif

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="/admin/san-pham" class="btn btn-secondary" data-cancel-form="product-edit-form">Hủy</a>
            </div>
        </form>
    </div>

    <script>
        let variantIndex = {{ $product->variants->count() ?: 1 }};

        function addVariant() {
            const html = `<div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                    <input type="text" name="variants[${variantIndex}][sku]" class="form-control" placeholder="SKU" style="max-width: 150px;" required>
                    <input type="text" name="variants[${variantIndex}][size]" class="form-control" placeholder="Size (VD: 39)" style="max-width: 130px;">
                    <input type="text" name="variants[${variantIndex}][color_name]" class="form-control" placeholder="Tên màu (VD: Đen)" style="max-width: 150px;">
                    <input type="number" name="variants[${variantIndex}][stock]" class="form-control" placeholder="Số lượng" style="max-width: 120px;" min="0" required>
                    <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="Giá" style="max-width: 140px;" min="0" required>
                    <input type="number" name="variants[${variantIndex}][sale_price]" class="form-control" placeholder="Giá sale" style="max-width: 140px;" min="0">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="this.parentElement.remove()">Xóa</button>
                </div>`;
            document.getElementById('variants-wrapper').insertAdjacentHTML('beforeend', html);
            variantIndex++;
        }
    </script>
@endsection
