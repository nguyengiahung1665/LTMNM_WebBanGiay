@extends('layouts.app')

@section('title', request('sale') ? 'Sản phẩm Sale' : 'Sản phẩm')

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb"><li><a href="{{ route('home') }}">Trang chủ</a></li><li>{{ request('sale') ? 'Sale' : 'Sản phẩm' }}</li></ul>

    <header class="page-header">
        <div>
            <p class="eyebrow">Bộ sưu tập</p>
            <h1 class="page-title">{{ request('sale') ? 'SALE' : 'TẤT CẢ SẢN PHẨM' }}</h1>
            @if(request('search'))<p class="page-subtitle">Kết quả cho “{{ request('search') }}”</p>@endif
        </div>
    </header>

    <div class="catalog-layout">
        <aside class="catalog-sidebar" data-catalog-filters aria-label="Bộ lọc sản phẩm">
            <div class="filter-head">
                <h2>Bộ lọc</h2>
                <a href="{{ route('products.index') }}" class="filter-clear">Xóa tất cả</a>
                <button type="button" class="icon-button filter-mobile-button" data-filter-close aria-label="Đóng bộ lọc"><x-icon name="close" /></button>
            </div>

            <form action="{{ route('products.index') }}" method="GET">
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                @if(request('sale'))<input type="hidden" name="sale" value="1">@endif
                @if(request('featured'))<input type="hidden" name="featured" value="1">@endif

                <section class="filter-group">
                    <h3>Danh mục</h3>
                    <div class="filter-links">
                        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="filter-link {{ request('category') ? '' : 'is-active' }}">Tất cả</a>
                        @foreach($categories as $category)
                            <a href="{{ request()->fullUrlWithQuery(['category' => $category->slug, 'page' => null]) }}" class="filter-link {{ request('category') === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
                        @endforeach
                    </div>
                </section>

                <section class="filter-group">
                    <h3>Thương hiệu</h3>
                    <div class="filter-links">
                        <a href="{{ request()->fullUrlWithQuery(['brand' => null, 'page' => null]) }}" class="filter-link {{ request('brand') ? '' : 'is-active' }}">Tất cả</a>
                        @foreach($brands as $brand)
                            <a href="{{ request()->fullUrlWithQuery(['brand' => $brand->slug, 'page' => null]) }}" class="filter-link {{ request('brand') === $brand->slug ? 'is-active' : '' }}">{{ $brand->name }}</a>
                        @endforeach
                    </div>
                </section>

                @if($sizes->isNotEmpty())
                <section class="filter-group">
                    <h3>Kích cỡ</h3>
                    <div class="filter-chips">
                        @foreach($sizes as $size)<a href="{{ request()->fullUrlWithQuery(['size' => request('size') === $size ? null : $size, 'page' => null]) }}" class="filter-chip {{ request('size') === $size ? 'is-active' : '' }}">{{ $size }}</a>@endforeach
                    </div>
                </section>
                @endif

                @if($colors->isNotEmpty())
                <section class="filter-group">
                    <h3>Màu sắc</h3>
                    <div class="filter-links">
                        @foreach($colors as $color)
                            <a href="{{ request()->fullUrlWithQuery(['color' => request('color') === $color->color_name ? null : $color->color_name, 'page' => null]) }}" class="filter-link {{ request('color') === $color->color_name ? 'is-active' : '' }}"><span><i class="color-dot" style="background:{{ $color->color_code ?: '#e5e5e5' }}"></i>{{ $color->color_name }}</span></a>
                        @endforeach
                    </div>
                </section>
                @endif

                <section class="filter-group">
                    <h3>Khoảng giá</h3>
                    <div class="price-inputs">
                        <input type="number" name="min_price" min="0" class="form-control" placeholder="Từ" value="{{ request('min_price') }}" aria-label="Giá thấp nhất">
                        <input type="number" name="max_price" min="0" class="form-control" placeholder="Đến" value="{{ request('max_price') }}" aria-label="Giá cao nhất">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" style="margin-top:12px">Áp dụng</button>
                </section>
            </form>
        </aside>

        <div class="catalog-results">
            <div class="catalog-toolbar">
                <button type="button" class="btn btn-secondary btn-sm filter-mobile-button" data-filter-open><x-icon name="filter" size="17" /> Bộ lọc</button>
                <span class="catalog-count"><strong>{{ $products->total() }}</strong> sản phẩm</span>
                <div class="catalog-sort">
                    <label for="catalog-sort">Sắp xếp</label>
                    <select id="catalog-sort" data-navigate-select>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest', 'page' => null]) }}" @selected(request('sort', 'latest') === 'latest')>Mới nhất</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc', 'page' => null]) }}" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc', 'page' => null]) }}" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'best_selling', 'page' => null]) }}" @selected(request('sort') === 'best_selling')>Bán chạy</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'name_asc', 'page' => null]) }}" @selected(request('sort') === 'name_asc')>Tên A–Z</option>
                    </select>
                </div>
            </div>

            @if($products->isNotEmpty())
                <div class="product-grid">
                    @foreach($products as $product)<x-product-card :product="$product" :wished="in_array($product->id, $wishlistProductIds)" />@endforeach
                </div>
                {{ $products->links('partials.pagination') }}
            @else
                <div class="empty-state"><div class="empty-state__icon"><x-icon name="search" size="30" /></div><h2>Không tìm thấy sản phẩm</h2><p>Hãy thử thay đổi từ khóa hoặc bỏ bớt một vài bộ lọc.</p><a href="{{ route('products.index') }}" class="btn btn-primary">Xem tất cả sản phẩm</a></div>
            @endif
        </div>
    </div>
</div>
@endsection
