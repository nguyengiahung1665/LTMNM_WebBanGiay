@extends('admin.layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
    <div class="page-header">
        <h2>➕ Thêm danh mục</h2>
        <a href="/admin/danh-muc" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="/admin/danh-muc" id="category-form" data-unsaved-warning>
            @csrf
            <div class="form-group">
                <label>Tên danh mục *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="status" value="1" checked> Hiển thị
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Lưu</button>
                <a href="/admin/danh-muc" class="btn btn-secondary" data-cancel-form="category-form">Hủy</a>
            <button type="button" class="btn btn-sm btn-secondary"
                onclick="ConfirmModal.open({title:'Xóa dữ liệu đã nhập',message:'Bạn có chắc muốn xóa toàn bộ dữ liệu đã nhập?',confirmText:'Xóa dữ liệu',type:'danger',onConfirm:()=>{AdminFormDraft.clear('category');document.getElementById('category-form').reset();}})">🗑️
                Xóa dữ liệu nháp</button>
        </form>
    </div>

    <script src="{{ asset('js/admin-form-draft.js') }}"></script>
    <script>
        if (window.AdminFormDraft) {
            AdminFormDraft.autoSave(document.getElementById('category-form'), 'category');
        }
    </script>
@endsection
