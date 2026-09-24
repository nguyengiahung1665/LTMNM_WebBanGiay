@extends('layouts.app')

@section('title', 'Đặt hàng')

@section('content')
    <div class="container" style="margin-top: 30px; min-height: 60vh;">
        <ul class="breadcrumb">
            <li><a href="/">Trang chủ</a></li>
            <li><a href="/gio-hang">Giỏ hàng</a></li>
            <li>Đặt hàng</li>
        </ul>

        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 30px;">📦 Đặt hàng</h1>

        <div class="checkout-layout">
            <!-- Thông tin giao hàng -->
            <div style="background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px;">Thông tin giao hàng</h3>

                <form method="POST" action="/dat-hang" id="checkout-form" data-validate novalidate>
                    @csrf

                    <div class="checkout-fields-grid">
                        <div class="form-group span-2">
                            <label for="full_name">Họ tên người nhận *</label>
                            <input type="text" name="full_name" id="full_name"
                                class="form-control @error('full_name') is-invalid @enderror"
                                value="{{ old('full_name', auth()->user()->name) }}" maxlength="255" required
                                data-required-message="Vui lòng nhập họ tên người nhận."
                                aria-invalid="{{ $errors->has('full_name') ? 'true' : 'false' }}">
                            <span class="field-error" data-error-for="full_name" @if(! $errors->has('full_name')) hidden @endif>{{ $errors->first('full_name') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', auth()->user()->email) }}" maxlength="255" required
                                data-required-message="Vui lòng nhập email."
                                data-email-message="Email không hợp lệ."
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                            <span class="field-error" data-error-for="email" @if(! $errors->has('email')) hidden @endif>{{ $errors->first('email') }}</span>
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại *</label>
                            <input type="tel" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', auth()->user()->phone) }}" maxlength="20" required
                                data-required-message="Vui lòng nhập số điện thoại."
                                aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}">
                            <span class="field-error" data-error-for="phone" @if(! $errors->has('phone')) hidden @endif>{{ $errors->first('phone') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="province_code">Tỉnh / Thành phố *</label>
                            <select id="province_code" name="province_code"
                                class="form-control @error('province_code') is-invalid @enderror" required
                                data-selected="{{ old('province_code', auth()->user()->province_code) }}"
                                data-required-message="Vui lòng chọn tỉnh/thành phố."
                                aria-invalid="{{ $errors->has('province_code') ? 'true' : 'false' }}">
                                <option value="">Chọn tỉnh/thành phố</option>
                            </select>
                            <span class="field-error" data-error-for="province_code" @if(! $errors->has('province_code')) hidden @endif>{{ $errors->first('province_code') }}</span>
                        </div>

                        <div class="form-group">
                            <label for="ward_code">Phường / Xã *</label>
                            <select id="ward_code" name="ward_code"
                                class="form-control @error('ward_code') is-invalid @enderror" required
                                data-selected="{{ old('ward_code', auth()->user()->ward_code) }}"
                                data-required-message="Vui lòng chọn phường/xã."
                                aria-invalid="{{ $errors->has('ward_code') ? 'true' : 'false' }}" disabled>
                                <option value="">Chọn phường/xã</option>
                            </select>
                            <span class="field-error" data-error-for="ward_code" @if(! $errors->has('ward_code')) hidden @endif>{{ $errors->first('ward_code') }}</span>
                        </div>

                        <div class="form-group span-2">
                            <label for="address_detail">Địa chỉ chi tiết *</label>
                            <input type="text" name="address_detail" id="address_detail"
                                class="form-control @error('address_detail') is-invalid @enderror"
                                value="{{ old('address_detail', auth()->user()->address_detail) }}" maxlength="255"
                                placeholder="Ví dụ: 123 Nguyễn Văn A..." required
                                data-required-message="Vui lòng nhập địa chỉ chi tiết."
                                aria-invalid="{{ $errors->has('address_detail') ? 'true' : 'false' }}">
                            <span class="field-error" data-error-for="address_detail" @if(! $errors->has('address_detail')) hidden @endif>{{ $errors->first('address_detail') }}</span>
                        </div>

                        <div class="form-group span-2">
                            <label for="note">Ghi chú (không bắt buộc)</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror"
                                maxlength="1000" placeholder="Ghi chú cho đơn hàng...">{{ old('note') }}</textarea>
                            <span class="field-error" data-error-for="note" @if(! $errors->has('note')) hidden @endif>{{ $errors->first('note') }}</span>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <p style="font-size: 13px; color: #999;">Phương thức thanh toán: <strong>Thanh toán khi nhận hàng
                                (COD)</strong></p>
                    </div>

                    <button type="submit" class="btn btn-primary w-full"
                        style="justify-content: center; padding: 14px; font-size: 16px; margin-top: 20px;">
                        ✅ Xác nhận đặt hàng
                    </button>
                </form>
            </div>

            <script>
                (function () {
                    const form = document.getElementById('checkout-form');
                    if (!form) return;

                    const STORAGE_KEY = 'checkout_form_data';
                    const fields = ['full_name', 'email', 'phone', 'province_code', 'ward_code', 'address_detail', 'note'];

                    function save() {
                        const data = {};
                        fields.forEach(name => {
                            const el = form.elements[name];
                            if (el) data[name] = el.value;
                        });
                        try {
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
                        } catch (e) {}
                    }

                    try {
                        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
                        fields.forEach(name => {
                            const el = form.elements[name];
                            if (el && saved[name] !== undefined) {
                                if (name === 'province_code' || name === 'ward_code') {
                                    el.dataset.selected = saved[name];
                                }
                                el.value = saved[name];
                            }
                        });
                    } catch (e) {}

                    form.addEventListener('input', save);
                    form.addEventListener('change', save);

                    form.addEventListener('submit', function (event) {
                        queueMicrotask(() => {
                            if (!event.defaultPrevented) localStorage.removeItem(STORAGE_KEY);
                        });
                    });
                })();
            </script>

            <!-- Tóm tắt đơn hàng -->
            <div class="checkout-summary"
                style="background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); height: fit-content; position: sticky; top: 100px;">
                <h3
                    style="font-size: 18px; font-weight: 600; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0;">
                    Đơn hàng của bạn</h3>

                @foreach($cartItems as $item)
                    <div style="display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f5f5f5;">
                        <img src="{{ $item->product->thumbnail_url }}" alt=""
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                        <div style="flex: 1;">
                            <div style="font-size: 13px; font-weight: 500;">{{ $item->product->name }}</div>
                            <div style="font-size: 12px; color: #999;">
                                @if($item->variant?->size) Size {{ $item->variant->size }} @endif
                                @if($item->variant?->color_name) / {{ $item->variant->color_name }} @endif
                                × {{ $item->quantity }}
                            </div>
                            <div style="font-size: 13px; font-weight: 600; color: #e94560;">
                                {{ number_format($item->subtotal) }}₫
                            </div>
                        </div>
                    </div>
                @endforeach

                <div style="margin-top: 16px;">
                    <div
                        style="display: flex; justify-content: space-between; font-size: 14px; color: #666; margin-bottom: 8px;">
                        <span>Tạm tính</span>
                        <span>{{ number_format($subtotal) }}₫</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; font-size: 14px; color: #666; margin-bottom: 8px;">
                        <span>Phí vận chuyển</span>
                        <span>{{ $shippingFee > 0 ? number_format($shippingFee) . '₫' : 'Miễn phí' }}</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; font-size: 22px; font-weight: 700; padding-top: 12px; border-top: 2px solid #f0f0f0;">
                        <span>Tổng cộng</span>
                        <span style="color: #e94560;">{{ number_format($total) }}₫</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script id="vietnam-addresses-data" type="application/json">{!! json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
