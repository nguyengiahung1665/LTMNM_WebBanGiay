@extends('admin.layouts.admin')

@section('title', 'Thêm sản phẩm')

@section('content')
    <div class="page-header">
        <h2>➕ Thêm sản phẩm</h2>
        <a href="/admin/san-pham" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="card">
        <form method="POST" action="/admin/san-pham" enctype="multipart/form-data" id="product-form" data-unsaved-warning>
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Tên sản phẩm *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <small style="color: red;">{{ $message }}</small> @enderror
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Danh mục *</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">— Chọn —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Thương hiệu *</label>
                        <select name="brand_id" class="form-control" required>
                            <option value="">— Chọn —</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Giá gốc *</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price') }}" required min="0">
                </div>
                <div class="form-group">
                    <label>Giá khuyến mãi</label>
                    <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price') }}" min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh đại diện</label>
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                <small style="color: #999;">Chọn file ảnh (JPEG, PNG, JPG, GIF, WebP, tối đa 2MB).</small>
                @error('thumbnail') <small style="color: red;">{{ $message }}</small> @enderror
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 16px;">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="status" value="1" checked> Hiển thị
                </label>
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="featured" value="1"> Nổi bật
                </label>
            </div>

            <hr style="margin: 20px 0;">

            <h4 style="margin-bottom: 16px;">📏🎨 Biến thể (Size + Màu + Tồn kho)</h4>
            <p style="font-size: 13px; color: #999; margin-bottom: 8px;">
                Mỗi dòng là 1 tổ hợp size + màu cụ thể với số lượng tồn kho riêng của tổ hợp đó.
                Có thể để trống Size hoặc Màu nếu sản phẩm không phân loại theo chiều đó.
            </p>
            <div id="variants-wrapper">
                <div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                    <input type="text" name="variants[0][sku]" class="form-control" placeholder="SKU" style="max-width: 150px;" required>
                    <input type="text" name="variants[0][size]" class="form-control" placeholder="Size (VD: 39)" style="max-width: 130px;">
                    <input type="text" name="variants[0][color_name]" class="form-control" placeholder="Tên màu (VD: Đen)" style="max-width: 150px;">
                    <input type="number" name="variants[0][stock]" class="form-control" placeholder="Số lượng" style="max-width: 120px;" min="0" required>
                    <input type="number" name="variants[0][price]" class="form-control" placeholder="Giá" style="max-width: 140px;" min="0" required>
                    <input type="number" name="variants[0][sale_price]" class="form-control" placeholder="Giá sale" style="max-width: 140px;" min="0">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="removeVariant(this)">Xóa</button>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="addVariant()">+ Thêm biến thể</button>

            <hr style="margin: 20px 0;">

            <h4 style="margin-bottom: 16px;">🖼️ Ảnh gallery (upload nhiều file)</h4>
            <div id="images-wrapper">
                <div class="image-row" style="margin-bottom: 8px;">
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple style="padding: 8px;">
                </div>
            </div>
            <small style="color: #999;">Có thể chọn nhiều file cùng lúc (JPEG, PNG, JPG, GIF, WebP, tối đa 2MB mỗi
                file).</small>
            <small style="color: #e94560; display: block; margin-top: 6px;">Lưu ý: dữ liệu form được tự động lưu khi
                trang bị load lại, nhưng ảnh cần chọn lại sau khi load lại trang.</small>
            @error('images.*') <small style="color: red;">{{ $message }}</small> @enderror

            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                <a href="/admin/san-pham" class="btn btn-secondary" data-cancel-form="product-form">Hủy</a>
                <button type="button" class="btn btn-sm btn-secondary"
                    onclick="clearDraft()">🗑️ Xóa dữ liệu nháp</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/admin-form-draft.js') }}"></script>
    <script>
        let variantIndex = 1;

        function variantRowHtml(i) {
            return `<div class="variant-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: center;">
                    <input type="text" name="variants[${i}][sku]" class="form-control" placeholder="SKU" style="max-width: 150px;" required>
                    <input type="text" name="variants[${i}][size]" class="form-control" placeholder="Size (VD: 39)" style="max-width: 130px;">
                    <input type="text" name="variants[${i}][color_name]" class="form-control" placeholder="Tên màu (VD: Đen)" style="max-width: 150px;">
                    <input type="number" name="variants[${i}][stock]" class="form-control" placeholder="Số lượng" style="max-width: 120px;" min="0" required>
                    <input type="number" name="variants[${i}][price]" class="form-control" placeholder="Giá" style="max-width: 140px;" min="0" required>
                    <input type="number" name="variants[${i}][sale_price]" class="form-control" placeholder="Giá sale" style="max-width: 140px;" min="0">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="removeVariant(this)">Xóa</button>
                </div>`;
        }

        function addVariant() {
            document.getElementById('variants-wrapper').insertAdjacentHTML('beforeend', variantRowHtml(variantIndex));
            variantIndex++;
        }

        function removeVariant(btn) {
            btn.parentElement.remove();
            if (window.AdminFormDraft) {
                AdminFormDraft.save(document.getElementById('product-form'), 'product');
            }
        }

        function clearDraft() {
            ConfirmModal.open({
                title: 'Xóa dữ liệu đã nhập',
                message: 'Bạn có chắc muốn xóa toàn bộ dữ liệu đã nhập trong form này?',
                confirmText: 'Xóa dữ liệu',
                type: 'danger',
                onConfirm: () => {
                    if (window.AdminFormDraft) AdminFormDraft.clear('product');
                    document.getElementById('product-form').reset();
                }
            });
        }

        const productForm = document.getElementById('product-form');
        if (productForm && window.AdminFormDraft) {
            AdminFormDraft.autoSave(productForm, 'product', function (form, key) {
                var data = AdminFormDraft.get(key);
                if (!data) return;

                // Khôi phục các trường thường (text, select, checkbox)
                AdminFormDraft.restore(form, key);

                // Tạo lại các dòng biến thể đã thêm thêm (index >= 1)
                var wrapper = document.getElementById('variants-wrapper');
                var maxIdx = 0;
                for (var name in data) {
                    var m = name.match(/^variants\[(\d+)\]\[(?:size|color_name|stock)\]$/);
                    if (m) maxIdx = Math.max(maxIdx, parseInt(m[1], 10));
                }
                for (var i = 1; i <= maxIdx; i++) {
                    if (data['variants[' + i + '][size]'] !== undefined ||
                        data['variants[' + i + '][color_name]'] !== undefined ||
                        data['variants[' + i + '][stock]'] !== undefined) {
                        wrapper.insertAdjacentHTML('beforeend', variantRowHtml(i));
                    }
                }
                // Đổ giá trị cho các dòng vừa tạo lại
                AdminFormDraft.restore(form, key);
                variantIndex = maxIdx + 1;
            });
        }
    </script>
@endsection
