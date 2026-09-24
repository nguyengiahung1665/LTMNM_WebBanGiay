@extends('admin.layouts.admin')

@section('title', 'Quản lý Thương hiệu')

@section('content')
    <div class="page-header">
        <h2>🏷️ Quản lý Thương hiệu</h2>
        <a href="/admin/thuong-hieu/tao" class="btn btn-primary">+ Thêm thương hiệu</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên thương hiệu</th>
                        <th>Slug</th>
                        <th>Số sản phẩm</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brands as $brand)
                        <tr>
                            <td>{{ $brand->id }}</td>
                            <td style="font-weight: 500;">{{ $brand->name }}</td>
                            <td style="font-size: 13px; color: #666;">{{ $brand->slug }}</td>
                            <td>{{ $brand->products()->count() }}</td>
                            <td>
                                <span class="badge badge-{{ $brand->status ? 'green' : 'red' }}">
                                    {{ $brand->status ? 'Hiện' : 'Ẩn' }}
                                </span>
                            </td>
                            <td>
                                <a href="/admin/thuong-hieu/{{ $brand->id }}/sua" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" action="/admin/thuong-hieu/{{ $brand->id }}/xoa" style="display:inline" data-confirm-title="Xóa thương hiệu" data-confirm="Bạn có chắc muốn xóa thương hiệu '{{ $brand->name }}'?" data-confirm-text="Xóa" data-confirm-type="danger">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $brands->links() }}</div>
    </div>
@endsection
