@props(['product', 'featured' => false, 'wished' => false, 'removeOnToggle' => false])
<article class="product-card">
    <div class="product-card__media">
        <a href="{{ route('products.show', $product->slug) }}" aria-label="Xem {{ $product->name }}">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" loading="lazy" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}">
        </a>
        <div class="product-card__badges">
            @if($product->sale_price)<span class="product-badge product-badge--sale">-{{ $product->sale_percentage }}%</span>@endif
            @if($featured || $product->featured)<span class="product-badge">Nổi bật</span>@endif
        </div>
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $product->id) }}" class="product-card__wishlist" data-wishlist-form data-remove-card="{{ $removeOnToggle ? '1' : '0' }}">
                @csrf
                <button type="submit" class="icon-button {{ $wished ? 'is-active' : '' }}" aria-label="{{ $wished ? 'Bỏ khỏi' : 'Thêm vào' }} yêu thích" aria-pressed="{{ $wished ? 'true' : 'false' }}">
                    <x-icon name="heart" size="19" />
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="product-card__wishlist icon-button" aria-label="Đăng nhập để thêm vào yêu thích">
                <x-icon name="heart" size="19" />
            </a>
        @endauth
    </div>
    <div class="product-card__body">
        <div class="product-card__eyebrow">{{ $product->brand->name ?? $product->category->name ?? 'SneakerShop' }}</div>
        <h3 class="product-card__name"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="product-card__meta">
            <div class="price-group">
                <span class="price-current">{{ number_format($product->final_price) }}₫</span>
                @if($product->sale_price)<span class="price-old">{{ number_format($product->price) }}₫</span>@endif
            </div>
            @if($product->avg_rating > 0)
                <span class="rating"><x-icon name="star" size="14" /> {{ $product->avg_rating }}</span>
            @endif
        </div>
    </div>
</article>
