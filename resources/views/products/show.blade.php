@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb">
        <li><a href="{{ route('home') }}">Trang chủ</a></li>
        <li><a href="{{ route('products.index') }}">Sản phẩm</a></li>
        @if($product->category)<li><a href="{{ route('category.products', $product->category->slug) }}">{{ $product->category->name }}</a></li>@endif
        <li>{{ $product->name }}</li>
    </ul>

    <div class="product-detail">
        <div class="product-gallery">
            <div class="gallery-main"><img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" id="main-product-image" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"></div>
            @if($product->images->isNotEmpty())
                <div class="gallery-thumbs">
                    <button type="button" class="gallery-thumb is-active" data-gallery-thumb data-src="{{ $product->thumbnail_url }}" data-alt="{{ $product->name }}"><img src="{{ $product->thumbnail_url }}" alt="Ảnh chính {{ $product->name }}" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"></button>
                    @foreach($product->images as $image)
                        <button type="button" class="gallery-thumb" data-gallery-thumb data-src="{{ $image->image_url }}" data-alt="{{ $product->name }} - ảnh {{ $loop->iteration + 1 }}"><img src="{{ $image->image_url }}" alt="{{ $product->name }} - ảnh {{ $loop->iteration + 1 }}" loading="lazy" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="detail-info">
            <div class="detail-info__brand">{{ $product->brand->name ?? $product->category->name ?? 'SneakerShop' }}</div>
            <h1>{{ $product->name }}</h1>
            <div class="detail-rating">
                <span class="rating"><x-icon name="star" size="15" /> {{ $product->avg_rating ?: 'Mới' }}</span>
                <span>·</span><a href="#reviews">{{ $product->reviews->count() }} đánh giá</a>
            </div>
            <div class="detail-price">
                <span class="price-current">{{ number_format($product->final_price) }}₫</span>
                @if($product->sale_price)<span class="price-old">{{ number_format($product->price) }}₫</span><span class="product-badge product-badge--sale">-{{ $product->sale_percentage }}%</span>@endif
            </div>
            <div class="detail-description">{{ $product->description ?: 'Thiết kế hiện đại, dễ phối đồ và phù hợp cho nhịp sống năng động mỗi ngày.' }}</div>

            @php
                $sizes = $product->variants->pluck('size')->filter()->unique()->values();
                $colors = $product->variants->filter(fn($variant) => $variant->color_name)
                    ->unique(fn($variant) => $variant->color_name.'|'.$variant->color_code)
                    ->map(fn($variant) => ['color_name' => $variant->color_name, 'color_code' => $variant->color_code])->values();
                $variantsJson = $product->variants->map(fn($variant) => [
                    'id' => $variant->id, 'size' => $variant->size, 'color_name' => $variant->color_name,
                    'color_code' => $variant->color_code, 'stock' => $variant->stock,
                ])->toJson();
                $wished = in_array($product->id, $wishlistProductIds);
            @endphp

            <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form" data-variants="{{ $variantsJson }}" data-authenticated="{{ auth()->check() ? '1' : '0' }}" data-login-url="{{ route('login') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="product_variant_id" id="selected-variant" value="">

                @if($sizes->isNotEmpty())
                    <div class="option-block"><div class="option-block__head"><strong>Chọn kích cỡ</strong><span>Kích cỡ hết hàng sẽ bị khóa</span></div><div class="variant-options">@foreach($sizes as $size)<button type="button" class="variant-option" data-size="{{ $size }}">{{ $size }}</button>@endforeach</div></div>
                @endif

                @if($colors->isNotEmpty())
                    <div class="option-block"><div class="option-block__head"><strong>Chọn màu</strong></div><div class="variant-options">@foreach($colors as $color)<button type="button" class="variant-option" data-color-name="{{ $color['color_name'] }}" data-color-code="{{ $color['color_code'] }}"><i class="color-dot" style="background:{{ $color['color_code'] ?: '#e5e5e5' }}"></i>{{ $color['color_name'] }}</button>@endforeach</div></div>
                @endif

                <p class="variant-stock" id="variant-stock-note" aria-live="polite"></p>
                <div class="quantity-row"><strong class="field-label">Số lượng</strong><span class="quantity-control"><button type="button" id="qty-minus" aria-label="Giảm số lượng">−</button><input type="number" inputmode="numeric" name="quantity" id="qty" value="1" min="1" max="1" aria-label="Số lượng"><button type="button" id="qty-plus" aria-label="Tăng số lượng">+</button></span></div>

                <div class="purchase-row">
                    @if($product->variants->where('stock', '>', 0)->isEmpty())
                        <button type="button" class="btn btn-primary btn-lg" disabled>Tạm hết hàng</button>
                    @else
                        <button type="submit" class="btn btn-primary btn-lg"><x-icon name="bag" /> Thêm vào giỏ hàng</button>
                    @endif
                    <button type="submit" form="product-wishlist-form" class="icon-button {{ $wished ? 'is-active' : '' }}" aria-label="{{ $wished ? 'Bỏ khỏi' : 'Thêm vào' }} yêu thích" aria-pressed="{{ $wished ? 'true' : 'false' }}"><x-icon name="heart" /></button>
                </div>
            </form>
            @auth<form method="POST" action="{{ route('wishlist.toggle', $product->id) }}" id="product-wishlist-form" data-wishlist-form>@csrf</form>@else<form action="{{ route('login') }}" id="product-wishlist-form" method="GET"></form>@endauth

            <div class="detail-trust">
                <div><x-icon name="truck" /><span>Giao hàng nhanh</span></div>
                <div><x-icon name="refresh" /><span>Hỗ trợ đổi trả</span></div>
                <div><x-icon name="shield" /><span>Chính hãng</span></div>
            </div>
        </div>
    </div>

    <section class="section" id="reviews">
        <div class="section-heading"><div><p class="eyebrow">Trải nghiệm thực tế</p><h2>Đánh giá sản phẩm</h2></div></div>
        <div class="reviews-layout">
            <div class="review-summary">
                <div class="review-score">{{ $product->avg_rating ?: '—' }}</div>
                <div class="rating" style="margin:10px 0"><x-icon name="star" size="17" /> {{ $product->reviews->count() }} đánh giá</div>
                <p class="page-subtitle">Phản hồi từ khách hàng đã mua sản phẩm.</p>
            </div>
            <div>
                @auth
                    @php
                        $hasBought = auth()->user()->orders()->whereHas('items', fn($query) => $query->where('product_id', $product->id))->exists();
                        $hasReviewed = auth()->user()->reviews()->where('product_id', $product->id)->exists();
                    @endphp
                    @if($hasBought && !$hasReviewed)
                        <div class="review-form" style="margin-bottom:18px">
                            <h3>Viết đánh giá</h3>
                            <form method="POST" action="{{ route('reviews.store', $product->slug) }}">@csrf
                                <div class="form-group"><label>Điểm đánh giá</label><div class="star-rating">@for($rating = 5; $rating >= 1; $rating--)<input type="radio" name="rating" value="{{ $rating }}" id="star{{ $rating }}" @checked($rating === 5)><label for="star{{ $rating }}">★</label>@endfor</div></div>
                                <div class="form-group"><label for="comment">Nhận xét</label><textarea name="comment" id="comment" class="form-control" maxlength="500" placeholder="Chia sẻ cảm nhận của bạn..."></textarea></div>
                                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="alert"><a href="{{ route('login') }}" class="auth-link">Đăng nhập</a> để viết đánh giá sau khi mua hàng.</div>
                @endauth

                <div class="review-list">
                    @forelse($product->reviews as $review)
                        <article class="review-card"><div class="review-card__head"><strong>{{ $review->user->name ?? 'Khách hàng' }}</strong><span class="date">{{ $review->created_at->format('d/m/Y') }}</span></div><div class="stars" aria-label="{{ $review->rating }} trên 5 sao">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>@if($review->comment)<div class="comment">{{ $review->comment }}</div>@endif</article>
                    @empty
                        <div class="empty-state"><h2>Chưa có đánh giá</h2><p>Hãy là người đầu tiên chia sẻ trải nghiệm về sản phẩm này.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
    <section class="section">
        <div class="section-heading"><div><p class="eyebrow">Có thể bạn sẽ thích</p><h2>Sản phẩm liên quan</h2></div><a href="{{ $product->category ? route('category.products', $product->category->slug) : route('products.index') }}" class="text-link">Xem thêm <x-icon name="arrow-right" size="17" /></a></div>
        <div class="product-grid">@foreach($relatedProducts as $related)<x-product-card :product="$related" :wished="in_array($related->id, $wishlistProductIds)" />@endforeach</div>
    </section>
    @endif
</div>
@endsection
