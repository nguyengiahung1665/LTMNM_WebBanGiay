@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb"><li><a href="{{ route('home') }}">Trang chủ</a></li><li><a href="{{ route('cart.index') }}">Giỏ hàng</a></li><li>Thanh toán</li></ul>
    <header class="page-header"><div><p class="eyebrow">Bước cuối cùng</p><h1 class="page-title">THANH TOÁN</h1><p class="page-subtitle">Kiểm tra thông tin trước khi xác nhận đơn hàng.</p></div></header>

    <div class="checkout-layout">
        <form method="POST" action="{{ route('checkout.place') }}" id="checkout-form" class="checkout-card" data-validate novalidate>
            @csrf
            <h2>Thông tin giao hàng</h2>
            <div class="checkout-fields-grid">
                <div class="form-group span-2"><label for="full_name">Họ và tên *</label><input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', auth()->user()->name) }}" maxlength="255" required data-required-message="Vui lòng nhập họ tên người nhận." aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}"><span class="field-error" data-error-for="full_name" @if(!$errors->has('full_name')) hidden @endif>{{ $errors->first('full_name') }}</span></div>
                <div class="form-group"><label for="email">Email *</label><input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" maxlength="255" required data-required-message="Vui lòng nhập email." data-email-message="Email không hợp lệ." aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"><span class="field-error" data-error-for="email" @if(!$errors->has('email')) hidden @endif>{{ $errors->first('email') }}</span></div>
                <div class="form-group"><label for="phone">Số điện thoại *</label><input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" maxlength="20" required data-required-message="Vui lòng nhập số điện thoại." aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"><span class="field-error" data-error-for="phone" @if(!$errors->has('phone')) hidden @endif>{{ $errors->first('phone') }}</span></div>
                <div class="form-group"><label for="province_code">Tỉnh / Thành phố *</label><select id="province_code" name="province_code" class="form-control @error('province_code') is-invalid @enderror" required data-selected="{{ old('province_code', auth()->user()->province_code) }}" data-required-message="Vui lòng chọn tỉnh/thành phố." aria-invalid="{{ $errors->has('province_code') ? 'true' : 'false' }}"><option value="">Chọn tỉnh/thành phố</option></select><span class="field-error" data-error-for="province_code" @if(!$errors->has('province_code')) hidden @endif>{{ $errors->first('province_code') }}</span></div>
                <div class="form-group"><label for="ward_code">Phường / Xã *</label><select id="ward_code" name="ward_code" class="form-control @error('ward_code') is-invalid @enderror" required data-selected="{{ old('ward_code', auth()->user()->ward_code) }}" data-required-message="Vui lòng chọn phường/xã." aria-invalid="{{ $errors->has('ward_code') ? 'true' : 'false' }}" disabled><option value="">Chọn phường/xã</option></select><span class="field-error" data-error-for="ward_code" @if(!$errors->has('ward_code')) hidden @endif>{{ $errors->first('ward_code') }}</span></div>
                <div class="form-group span-2"><label for="address_detail">Địa chỉ chi tiết *</label><input type="text" name="address_detail" id="address_detail" class="form-control @error('address_detail') is-invalid @enderror" value="{{ old('address_detail', auth()->user()->address_detail) }}" maxlength="255" placeholder="Số nhà, tên đường..." required data-required-message="Vui lòng nhập địa chỉ chi tiết." aria-invalid="{{ $errors->has('address_detail') ? 'true' : 'false' }}"><span class="field-error" data-error-for="address_detail" @if(!$errors->has('address_detail')) hidden @endif>{{ $errors->first('address_detail') }}</span></div>
                <div class="form-group span-2"><label for="note">Ghi chú</label><textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" maxlength="1000" placeholder="Ghi chú cho đơn hàng (không bắt buộc)">{{ old('note') }}</textarea><span class="field-error" data-error-for="note" @if(!$errors->has('note')) hidden @endif>{{ $errors->first('note') }}</span></div>
            </div>

            <h2 style="margin-top:28px">Phương thức thanh toán</h2>
            <label class="payment-option"><input type="radio" name="payment_method_display" value="cod" checked><x-icon name="package" /><span><strong>Thanh toán khi nhận hàng (COD)</strong><small style="display:block;color:var(--color-secondary);margin-top:3px">Thanh toán trực tiếp cho nhân viên giao hàng.</small></span></label>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:24px">Đặt hàng · {{ number_format($total) }}₫ <x-icon name="arrow-right" /></button>
        </form>

        <aside class="summary-card checkout-summary">
            <h2>Đơn hàng của bạn</h2>
            <div class="order-mini-list">
                @foreach($cartItems as $item)
                    <div class="order-mini-item"><img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"><div><strong>{{ $item->product->name }}</strong><small>@if($item->variant?->size)Size {{ $item->variant->size }}@endif @if($item->variant?->color_name) · {{ $item->variant->color_name }}@endif · SL {{ $item->quantity }}</small></div><strong>{{ number_format($item->subtotal) }}₫</strong></div>
                @endforeach
            </div>
            <div class="summary-line"><span>Tạm tính</span><strong>{{ number_format($subtotal) }}₫</strong></div>
            <div class="summary-line"><span>Phí vận chuyển</span><strong>{{ $shippingFee ? number_format($shippingFee).'₫' : 'Miễn phí' }}</strong></div>
            <div class="summary-total"><span>Tổng cộng</span><strong>{{ number_format($total) }}₫</strong></div>
            <p class="page-subtitle"><x-icon name="shield" size="15" /> Thông tin của bạn được bảo mật.</p>
        </aside>
    </div>
</div>
<script id="vietnam-addresses-data" type="application/json">{!! json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('checkout-form'); if (!form) return;
    const key = 'checkout_form_data'; const fields = ['full_name','email','phone','province_code','ward_code','address_detail','note'];
    try { const saved = JSON.parse(localStorage.getItem(key) || '{}'); fields.forEach(name => { const field=form.elements[name]; if(field && saved[name] !== undefined && !field.value){ field.value=saved[name]; if(name.includes('_code')) field.dataset.selected=saved[name]; } }); } catch {}
    const save = () => { const data={}; fields.forEach(name => { if(form.elements[name]) data[name]=form.elements[name].value; }); try { localStorage.setItem(key, JSON.stringify(data)); } catch {} };
    form.addEventListener('input', save); form.addEventListener('change', save); form.addEventListener('submit', event => queueMicrotask(() => { if(!event.defaultPrevented) localStorage.removeItem(key); }));
})();
</script>
@endpush
