@extends('layouts.app')

@section('title', 'Giày thể thao chính hãng')

@section('content')
<section class="hero">
    <div class="container hero__grid">
        <div class="hero__content">
            <p class="eyebrow">Bộ sưu tập mới</p>
            <h1>BƯỚC RA.<br><span>BỨT PHÁ.</span></h1>
            <p class="hero__lead">Khám phá những đôi giày được chọn lọc cho từng chuyển động — từ đường phố đến đường chạy.</p>
            <div class="hero__actions">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">Mua sắm ngay <x-icon name="arrow-right" /></a>
                <a href="{{ route('products.index', ['featured' => 1]) }}" class="btn btn-outline btn-lg">Khám phá nổi bật</a>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="hero-visual__ring"></div>
            <svg class="hero-visual__shoe" viewBox="0 0 720 410" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M70 275c90 3 177-56 226-164 12-27 48-32 67-9 58 69 113 117 225 143 67 16 100 61 83 104-14 36-55 54-113 54H151c-68 0-116-38-112-82 2-24 13-43 31-46Z" />
                <path d="M249 210c51 46 105 72 169 85M305 145c48 51 99 82 162 100M405 196l62 49M126 331h521" stroke="#E94560" stroke-width="17" stroke-linecap="round" />
                <path d="M253 206 177 99M324 157l-49-77M401 200l-31-82" stroke="#D4D4D4" stroke-width="13" stroke-linecap="round" />
            </svg>
            <div class="hero-note"><strong>Thiết kế cho chuyển động</strong><span>Êm nhẹ · Linh hoạt · Bền bỉ</span></div>
        </div>
    </div>
</section>

<section class="benefit-strip" aria-label="Cam kết dịch vụ">
    <div class="container benefit-grid">
        <div class="benefit"><x-icon name="truck" size="25" />
            <div><strong>Miễn phí giao hàng</strong><span>Đơn hàng từ 500.000₫</span></div>
        </div>
        <div class="benefit"><x-icon name="refresh" size="25" />
            <div><strong>Đổi trả thuận tiện</strong><span>Hỗ trợ nhanh chóng</span></div>
        </div>
        <div class="benefit"><x-icon name="shield" size="25" />
            <div><strong>Sản phẩm chính hãng</strong><span>Cam kết chất lượng</span></div>
        </div>
    </div>
</section>

@if($categories->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Chọn phong cách</p>
                <h2>Danh mục nổi bật</h2>
            </div><a href="{{ route('products.index') }}" class="text-link">Xem tất cả <x-icon name="arrow-right" size="17" /></a>
        </div>
        <div class="category-grid">
            @foreach($categories->take(4) as $category)
            <a href="{{ route('category.products', $category->slug) }}" class="category-card">
                <span class="category-card__index">0{{ $loop->iteration }}</span>
                <h3>{{ $category->name }}</h3>
                <p>Khám phá bộ sưu tập <x-icon name="arrow-right" size="13" /></p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section section--surface">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Được yêu thích</p>
                <h2>Sản phẩm nổi bật</h2>
                <p>Những lựa chọn đang tạo nên nhịp điệu mùa này.</p>
            </div><a href="{{ route('products.index', ['featured' => 1]) }}" class="text-link">Xem tất cả <x-icon name="arrow-right" size="17" /></a>
        </div>
        <div class="product-grid">
            @forelse($featuredProducts as $product)
            <x-product-card :product="$product" :featured="true" :wished="in_array($product->id, $wishlistProductIds)" />
            @empty
            <div class="empty-state">
                <h2>Sản phẩm đang được cập nhật</h2>
                <p>Vui lòng quay lại sau để xem bộ sưu tập mới.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Vừa lên kệ</p>
                <h2>Mới nhất</h2>
            </div><a href="{{ route('products.index') }}" class="text-link">Mua sản phẩm mới <x-icon name="arrow-right" size="17" /></a>
        </div>
        <div class="product-grid">
            @foreach($newProducts as $product)<x-product-card :product="$product" :wished="in_array($product->id, $wishlistProductIds)" />@endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container editorial-banner">
        <div class="editorial-banner__content">
            <p class="eyebrow">Ưu đãi trong tuần</p>
            <h2>ĐI XA HƠN.<br>TRẢ ÍT HƠN.</h2>
            <p>Khám phá các thiết kế đang giảm giá và chọn đôi giày tiếp theo cho hành trình của bạn.</p>
            <a href="{{ route('products.index', ['sale' => 1]) }}" class="btn btn-primary btn-lg">Khám phá Sale <x-icon name="arrow-right" /></a>
        </div>
        <div class="editorial-banner__art" aria-hidden="true"></div>
    </div>
</section>

@if($saleProducts->isNotEmpty())
<section class="section section--surface">
    <div class="container">
        <div class="section-heading">
            <div><p class="eyebrow">Giá tốt hôm nay</p><h2>Sản phẩm đang sale</h2></div>
            <a href="{{ route('products.index', ['sale' => 1]) }}" class="text-link">Xem tất cả <x-icon name="arrow-right" size="17" /></a>
        </div>
        <div class="product-grid">@foreach($saleProducts as $product)<x-product-card :product="$product" :wished="in_array($product->id, $wishlistProductIds)" />@endforeach</div>
    </div>
</section>
@endif

@if($trendingProducts->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Xu hướng</p>
                <h2>Đang được săn đón</h2>
            </div>
        </div>
        <div class="product-grid">@foreach($trendingProducts as $product)<x-product-card :product="$product" :wished="in_array($product->id, $wishlistProductIds)" />@endforeach</div>
    </div>
</section>
@endif

@if($brands->isNotEmpty())
<section class="section section--surface">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Tên tuổi hàng đầu</p>
                <h2>Thương hiệu</h2>
            </div>
        </div>
        <div class="brand-grid">
            @foreach($brands->take(10) as $brand)
            <a href="{{ route('brand.products', $brand->slug) }}" class="brand-card"><span><strong>{{ $brand->name }}</strong><small>Xem bộ sưu tập</small></span></a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($bestSellerProducts->isNotEmpty())
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Lựa chọn của cộng đồng</p>
                <h2>Bán chạy nhất</h2>
            </div><a href="{{ route('products.index', ['sort' => 'best_selling']) }}" class="text-link">Xem tất cả <x-icon name="arrow-right" size="17" /></a>
        </div>
        <div class="product-grid">@foreach($bestSellerProducts as $product)<x-product-card :product="$product" :wished="in_array($product->id, $wishlistProductIds)" />@endforeach</div>
    </div>
</section>
@endif

<section class="section">
    <div class="container newsletter">
        <div>
            <p class="eyebrow">Sneaker notes</p>
            <h2>Đừng bỏ lỡ nhịp mới.</h2>
            <p>Nhận tư vấn sản phẩm, thông tin bộ sưu tập và ưu đãi mới từ SneakerShop.</p>
        </div>
        <a href="mailto:info@webanhang.com?subject=Đăng ký nhận tin SneakerShop" class="btn btn-primary btn-lg">Đăng ký nhận tin <x-icon name="mail" /></a>
    </div>
</section>
@endsection
