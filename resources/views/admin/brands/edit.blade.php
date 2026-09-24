@extends('admin.layouts.admin')

@section('title', 'Sửa thương hiệu')

@section('content')
    <div class="page-header">
        <h2>✏️ Sửa thương hiệu</h2>
        <a href="/admin/thuong-hieu" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="card" style="max-width: 600px;">
        <form method="POST" action="/admin/thuong-hieu/{{ $brand->id }}/sua" id="brand-edit-form" data-unsaved-warning>
            @csrf
            <div class="form-group">
                <label>Tên thương hiệu *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $brand->name) }}" required>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="status" value="1" {{ $brand->status ? 'checked' : '' }}> Hiển thị
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="/admin/thuong-hieu" class="btn btn-secondary" data-cancel-form="brand-edit-form">Hủy</a>
        </form>
    </div>
@endsection
