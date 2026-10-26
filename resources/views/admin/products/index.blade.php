@extends('admin.layouts.admin')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    <div class="page-header">
        <h2>👟 Quản lý Sản phẩm</h2>
        <a href="/admin/san-pham/tao" class="btn btn-primary">+ Thêm sản phẩm</a>
    </div>

    <div class="card">
        <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px;">
            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..."
                value="{{ request('search') }}" style="max-width: 300px;">
            <select name="category_id" class="form-control" style="max-width: 200px;" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Tìm</button>
            <a href="/admin/san-pham" class="btn btn-sm btn-secondary">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Thương hiệu</th>
                        <th>Giá</th>
                        <th>Giá KM</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td><img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"
                                    style="width: 50px; height: 50px; object-fit: contain; background:#f5f5f5; border-radius: 6px;"></td>
                            <td style="font-weight: 500;">{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>{{ $product->brand->name ?? '—' }}</td>
                            <td>{{ number_format($product->price) }}₫</td>
                            <td>{{ $product->sale_price ? number_format($product->sale_price) . '₫' : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $product->status ? 'green' : 'red' }}">
                                    {{ $product->status ? 'Hiện' : 'Ẩn' }}
                                </span>
                            </td>
                            <td>{{ $product->featured ? '⭐' : '—' }}</td>
                            <td>
                                <a href="/admin/san-pham/{{ $product->id }}/sua" class="btn btn-sm btn-warning">Sửa</a>
                                <form method="POST" action="/admin/san-pham/{{ $product->id }}/xoa" style="display:inline" data-confirm-title="Ngừng kinh doanh sản phẩm" data-confirm="Bạn có chắc muốn ngừng kinh doanh '{{ $product->name }}'? Lịch sử đơn hàng vẫn được giữ nguyên." data-confirm-text="Ngừng kinh doanh" data-confirm-type="danger">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $products->links() }}</div>
    </div>
@endsection
