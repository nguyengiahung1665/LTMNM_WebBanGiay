@extends('layouts.app')

@section('title', 'Đơn hàng '.$order->order_code)

@section('content')
@php
    $statusOrder = ['pending', 'processing', 'completed'];
    $currentIndex = array_search($order->status, $statusOrder, true);
    $statusLabels = ['pending' => 'Chờ xác nhận', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành'];
@endphp
<div class="container page-shell">
    <ul class="breadcrumb"><li><a href="{{ route('home') }}">Trang chủ</a></li><li><a href="{{ route('orders.index') }}">Đơn hàng</a></li><li>{{ $order->order_code }}</li></ul>
    <header class="page-header"><div><p class="eyebrow">Đặt ngày {{ $order->created_at->format('d/m/Y') }}</p><h1 class="page-title">#{{ $order->order_code }}</h1><p class="page-subtitle">Cập nhật gần nhất {{ $order->updated_at->format('d/m/Y H:i') }}</p></div><span id="order-status-badge" class="status-badge status-{{ $order->status }}">{{ $order->status_label }}</span></header>

    <div class="panel" style="margin-bottom:24px">
        @if($order->status === 'cancelled')
            <div class="status-badge status-cancelled">Đơn hàng đã hủy</div>
        @else
            <div class="status-flow">@foreach($statusOrder as $index => $status)<div class="status-step {{ $status === $order->status ? 'is-active' : (($currentIndex !== false && $index < $currentIndex) ? 'is-done' : '') }}">{{ $statusLabels[$status] }}</div>@endforeach</div>
        @endif
    </div>

    <div class="order-detail-layout">
        <section class="panel">
            <h2 style="margin-top:0">Sản phẩm đã đặt</h2>
            <div class="order-mini-list">
                @foreach($order->items as $item)
                    <div class="order-mini-item"><img src="{{ $item->product?->thumbnail_url ?? asset('images/product-placeholder.svg') }}" alt="{{ $item->product_name }}" data-product-image data-fallback-src="{{ asset('images/product-placeholder.svg') }}"><div><strong>{{ $item->product_name }}</strong><small>{{ $item->variant?->label ?? 'Phiên bản tiêu chuẩn' }} · SL {{ $item->quantity }}</small></div><strong>{{ number_format($item->subtotal) }}₫</strong></div>
                @endforeach
            </div>
        </section>

        <aside>
            <section class="summary-card" style="position:static;margin-bottom:18px"><h2>Thông tin giao hàng</h2><p><strong>{{ $order->full_name }}</strong></p><p class="page-subtitle">{{ $order->phone }}<br>{{ $order->email }}<br>{{ $order->address }}</p>@if($order->note)<p class="page-subtitle"><strong>Ghi chú:</strong> {{ $order->note }}</p>@endif</section>
            <section class="summary-card" style="position:static"><h2>Thanh toán</h2><div class="summary-line"><span>Tạm tính</span><strong>{{ number_format($order->total_price - $order->shipping_fee) }}₫</strong></div><div class="summary-line"><span>Phí vận chuyển</span><strong>{{ $order->shipping_fee ? number_format($order->shipping_fee).'₫' : 'Miễn phí' }}</strong></div><div class="summary-total"><span>Tổng cộng</span><strong>{{ number_format($order->total_price) }}₫</strong></div><p class="page-subtitle">Thanh toán khi nhận hàng (COD)</p></section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;
    window.Echo.private('order.{{ $order->id }}').listen('.status.updated', event => {
        const badge = document.getElementById('order-status-badge');
        badge.textContent = event.status_label;
        badge.className = 'status-badge status-' + event.status;
        window.Toast?.show('Trạng thái đơn hàng vừa được cập nhật.', 'info');
    });
});
</script>
@endpush
