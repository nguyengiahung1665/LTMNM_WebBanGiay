@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <div class="container" style="margin-top: 30px; min-height: 60vh;">
        <ul class="breadcrumb">
            <li><a href="/">Trang chủ</a></li>
            <li>Giỏ hàng</li>
        </ul>

        <h1 style="font-size: 28px; font-weight: 700; margin-bottom: 30px;">🛒 Giỏ hàng</h1>

        @if($cartItems->count() > 0)
            <div style="display: grid; grid-template-columns: 1fr 320px; gap: 30px;">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 80px;">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Size/Màu</th>
                                <th>Đơn giá</th>
                                <th style="width: 120px;">Số lượng</th>
                                <th>Thành tiền</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cartItems as $item)
                                <tr>
                                    <td>
                                        <img src="{{ $item->product->thumbnail_url }}" alt=""
                                            style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td>
                                        <a href="/san-pham/{{ $item->product->slug }}"
                                            style="color: #1a1a2e; text-decoration: none; font-weight: 500;">
                                            {{ $item->product->name }}
                                        </a>
                                    </td>
                                    <td style="font-size: 13px; color: #666;">
                                        @if($item->variant?->size) Size {{ $item->variant->size }}<br>@endif
                                        @if($item->variant?->color_name) Màu {{ $item->variant->color_name }}@endif
                                    </td>
                                    <td style="font-weight: 600;">{{ number_format($item->product->final_price) }}₫</td>
                                    <td>
                                        <form method="POST" action="/gio-hang/cap-nhat/{{ $item->id }}"
                                            style="display: flex; align-items: center; gap: 4px;">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99"
                                                onchange="this.form.submit()"
                                                style="width: 50px; text-align: center; padding: 6px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                                        </form>
                                    </td>
                                    <td style="font-weight: 600; color: #e94560;">{{ number_format($item->subtotal) }}₫</td>
                                    <td>
                                        <form method="POST" action="/gio-hang/xoa/{{ $item->id }}" data-confirm-title="Xóa khỏi giỏ hàng" data-confirm="Bạn có chắc muốn xóa '{{ $item->product->name }}' khỏi giỏ hàng?" data-confirm-text="Xóa" data-confirm-type="danger">
                                            @csrf
                                            <button type="submit"
                                                style="background: none; border: none; cursor: pointer; font-size: 18px; color: #dc3545;"
                                                aria-label="Xóa khỏi giỏ hàng">✕</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Tổng cộng -->
                <div
                    style="background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); height: fit-content; position: sticky; top: 100px;">
                    <h3
                        style="font-size: 18px; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #f0f0f0;">
                        Tổng cộng</h3>

                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #666;">
                        <span>Tạm tính ({{ $cartItems->sum('quantity') }} sản phẩm)</span>
                        <span>{{ number_format($subtotal) }}₫</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; color: #666;">
                        <span>Phí vận chuyển</span>
                        <span>{{ $subtotal >= 500000 ? 'Miễn phí' : '30.000₫' }}</span>
                    </div>
                    @php $shippingFee = $subtotal >= 500000 ? 0 : 30000; @endphp
                    <div
                        style="display: flex; justify-content: space-between; font-size: 20px; font-weight: 700; color: #1a1a2e; padding-top: 12px; border-top: 2px solid #f0f0f0;">
                        <span>Tổng cộng</span>
                        <span style="color: #e94560;">{{ number_format($subtotal + $shippingFee) }}₫</span>
                    </div>

                    <a href="/dat-hang" class="btn btn-primary w-full"
                        style="justify-content: center; padding: 14px; font-size: 16px; margin-top: 20px;">
                        Tiến hành đặt hàng →
                    </a>
                    <a href="/san-pham" class="btn btn-outline w-full" style="justify-content: center; margin-top: 10px;">← Tiếp
                        tục mua sắm</a>
                </div>
            </div>
        @else
            <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px;">
                <div style="font-size: 80px; margin-bottom: 16px;">🛒</div>
                <h2 style="margin-bottom: 8px;">Giỏ hàng trống</h2>
                <p style="color: #666; margin-bottom: 24px;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
                <a href="/san-pham" class="btn btn-primary">Mua sắm ngay</a>
            </div>
        @endif
    </div>
@endsection
