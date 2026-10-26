@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb">
        <li><a href="{{ route('home') }}">Trang chủ</a></li>
        <li>Giỏ hàng</li>
    </ul>
    <header class="page-header">
        <div>
            <p class="eyebrow">Đơn hàng của bạn</p>
            <h1 class="page-title">GIỎ HÀNG</h1>
            <p class="page-subtitle">{{ $cartItems->sum('quantity') }} sản phẩm đã chọn</p>
        </div>
    </header>

    @if($cartItems->isNotEmpty())
    @php $shippingFee = $subtotal >= 500000 ? 0 : 30000; @endphp
    <div class="two-column-layout">
        <div class="cart-list">
            @foreach($cartItems as $item)
            <article class="cart-item">
                <a href="{{ route('products.show', $item->product->slug) }}" class="cart-item__image"><img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"></a>
                <div class="cart-item__body">
                    <h2><a href="{{ route('products.show', $item->product->slug) }}">{{ $item->product->name }}</a></h2>
                    <div class="cart-item__variant">
                        @if($item->variant?->size)<span>Size {{ $item->variant->size }}</span>@endif
                        @if($item->variant?->color_name)<span> · {{ $item->variant->color_name }}</span>@endif
                    </div>
                    <div class="cart-item__bottom">
                        <span class="cart-item__price">{{ number_format($item->product->final_price) }}₫</span>
                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="form-actions">@csrf
                            <span class="quantity-control"><button type="button" data-cart-qty="minus" aria-label="Giảm số lượng">−</button><input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->variant?->stock ?? 99 }}" aria-label="Số lượng {{ $item->product->name }}"><button type="button" data-cart-qty="plus" aria-label="Tăng số lượng">+</button></span>
                            <button type="submit" class="btn btn-secondary btn-sm">Cập nhật</button>
                        </form>
                        <form method="POST" action="{{ route('wishlist.toggle', $item->product->id) }}" data-wishlist-form>@csrf<button type="submit" class="btn btn-secondary btn-sm"><x-icon name="heart" size="15" /> Lưu yêu thích</button></form>
                    </div>
                </div>
                <div class="cart-item__aside">
                    <strong class="cart-item__subtotal">{{ number_format($item->subtotal) }}₫</strong>
                    <form method="POST" action="{{ route('cart.remove', $item->id) }}" data-confirm-title="Xóa khỏi giỏ hàng" data-confirm="Bạn có chắc muốn xóa '{{ $item->product->name }}' khỏi giỏ hàng?" data-confirm-text="Xóa" data-confirm-type="danger">@csrf<button type="submit" class="icon-button remove-button" aria-label="Xóa {{ $item->product->name }}"><x-icon name="trash" size="18" /></button></form>
                </div>
            </article>
            @endforeach
        </div>

        <aside class="summary-card">
            <h2>Tóm tắt đơn hàng</h2>
            <div class="summary-line"><span>Tạm tính</span><strong>{{ number_format($subtotal) }}₫</strong></div>
            <div class="summary-line"><span>Phí vận chuyển</span><strong>{{ $shippingFee ? number_format($shippingFee).'₫' : 'Miễn phí' }}</strong></div>
            @if($shippingFee)<p class="page-subtitle">Mua thêm {{ number_format(500000 - $subtotal) }}₫ để được miễn phí giao hàng.</p>@endif
            <div class="summary-total"><span>Tổng cộng</span><strong>{{ number_format($subtotal + $shippingFee) }}₫</strong></div>
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg btn-block">Tiến hành đặt hàng <x-icon name="arrow-right" /></a>
            <a href="{{ route('products.index') }}" class="btn btn-secondary btn-block" style="margin-top:10px">Tiếp tục mua sắm</a>
        </aside>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state__icon"><x-icon name="bag" size="32" /></div>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <p>Khám phá bộ sưu tập và thêm những đôi giày phù hợp với phong cách của bạn.</p><a href="{{ route('products.index') }}" class="btn btn-primary">Khám phá sản phẩm</a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-cart-qty]').forEach(button => button.addEventListener('click', () => {
        const input = button.closest('.quantity-control').querySelector('input');
        const min = Number(input.min || 1),
            max = Number(input.max || 99),
            step = button.dataset.cartQty === 'plus' ? 1 : -1;
        input.value = Math.min(max, Math.max(min, Number(input.value || min) + step));
    }));
</script>
@endpush
