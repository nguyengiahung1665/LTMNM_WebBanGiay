@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb"><li><a href="{{ route('home') }}">Trang chủ</a></li><li>Đơn hàng</li></ul>
    <header class="page-header"><div><p class="eyebrow">Lịch sử mua sắm</p><h1 class="page-title">ĐƠN HÀNG CỦA TÔI</h1><p class="page-subtitle">Theo dõi trạng thái và xem lại sản phẩm đã đặt.</p></div></header>

    @if($orders->isNotEmpty())
        <div class="table-wrap"><table><thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Sản phẩm</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead><tbody>
            @foreach($orders as $order)
                <tr><td><strong>{{ $order->order_code }}</strong></td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td><td>{{ $order->items->count() }} sản phẩm</td><td><strong>{{ number_format($order->total_price) }}₫</strong></td><td><span class="status-badge status-{{ $order->status }}">{{ $order->status_label }}</span></td><td><a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary btn-sm">Chi tiết <x-icon name="arrow-right" size="14" /></a></td></tr>
            @endforeach
        </tbody></table></div>
        {{ $orders->links('partials.pagination') }}
    @else
        <div class="empty-state"><div class="empty-state__icon"><x-icon name="package" size="32" /></div><h2>Chưa có đơn hàng</h2><p>Đơn hàng sau khi đặt sẽ xuất hiện tại đây để bạn tiện theo dõi.</p><a href="{{ route('products.index') }}" class="btn btn-primary">Bắt đầu mua sắm</a></div>
    @endif
</div>
@endsection
