@extends('admin.layouts.admin')

@section('title', 'Quản lý Danh mục')

@section('content')
    <div class="page-header">
        <h2>📁 Quản lý Danh mục</h2>
        <a href="/admin/danh-muc/tao" class="btn btn-primary">+ Thêm danh mục</a>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Số sản phẩm</th>
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td>{{ $cat->id }}</td>
                            <td style="font-weight: 500;">{{ $cat->name }}</td>
                            <td style="font-size: 13px; color: #666;">{{ $cat->slug }}</td>
                            <td>{{ $cat->products()->count() }}</td>
                            <td>
                                <span class="badge badge-{{ $cat->status ? 'green' : 'red' }}">
                                    {{ $cat->status ? 'Hiện' : 'Ẩn' }}
                                </span>
                            </td>
                            <td>
                                <a href="/admin/danh-muc/{{ $cat->id }}/sua" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" action="/admin/danh-muc/{{ $cat->id }}/xoa" style="display:inline" data-confirm-title="Xóa danh mục" data-confirm="Bạn có chắc muốn xóa danh mục '{{ $cat->name }}'?" data-confirm-text="Xóa" data-confirm-type="danger">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $categories->links() }}</div>
    </div>
@endsection
