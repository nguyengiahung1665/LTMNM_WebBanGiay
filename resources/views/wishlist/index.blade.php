@extends('layouts.app')

@section('title', 'Sản phẩm yêu thích')

@section('content')
<div class="container page-shell">
    <ul class="breadcrumb"><li><a href="{{ route('home') }}">Trang chủ</a></li><li>Yêu thích</li></ul>
    <header class="page-header"><div><p class="eyebrow">Danh sách của bạn</p><h1 class="page-title">YÊU THÍCH</h1><p class="page-subtitle">{{ $wishlist->count() }} sản phẩm đã lưu</p></div></header>

    @if($wishlist->isNotEmpty())
        <div class="product-grid">
            @foreach($wishlist as $item)<x-product-card :product="$item->product" :wished="true" :remove-on-toggle="true" />@endforeach
        </div>
    @else
        <div class="empty-state"><div class="empty-state__icon"><x-icon name="heart" size="32" /></div><h2>Bạn chưa có sản phẩm yêu thích.</h2><p>Lưu lại những đôi giày bạn quan tâm để dễ dàng quay lại sau.</p><a href="{{ route('products.index') }}" class="btn btn-primary">Khám phá sản phẩm</a></div>
    @endif
</div>
@endsection
