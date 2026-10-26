@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container page-shell">
    <section class="success-card">
        <div class="success-card__icon"><x-icon name="check" size="42" /></div>
        <p class="eyebrow">Đơn hàng đã được ghi nhận</p>
        <h1 class="page-title" style="font-size:36px">ĐẶT HÀNG THÀNH CÔNG</h1>
        <p class="page-subtitle">Cảm ơn bạn đã mua hàng tại SneakerShop. Chúng tôi sẽ sớm liên hệ để xác nhận.</p>
        <div class="success-card__code">{{ $order->order_code }}</div>
        <div class="form-actions" style="justify-content:center"><a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary">Xem đơn hàng <x-icon name="arrow-right" /></a><a href="{{ route('products.index') }}" class="btn btn-secondary">Tiếp tục mua sắm</a></div>
    </section>
</div>
@endsection
