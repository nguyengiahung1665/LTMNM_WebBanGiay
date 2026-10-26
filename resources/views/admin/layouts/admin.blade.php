<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-user-logged-in" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="app-user-is-admin" content="{{ auth()->user()?->is_admin ? '1' : '0' }}">
    <title>@yield('title', 'Dashboard') — Admin SneakerShop</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
</head>
<body class="admin-shell">
    <aside class="sidebar">
        <h2>SNEAKER<span>SHOP</span></h2>
        <nav aria-label="Quản trị">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><x-icon name="grid" /> <span>Dashboard</span></a>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><x-icon name="package" /> <span>Sản phẩm</span></a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><x-icon name="filter" /> <span>Danh mục</span></a>
            <a href="{{ route('admin.brands.index') }}" class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"><x-icon name="star" /> <span>Thương hiệu</span></a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><x-icon name="bag" /> <span>Đơn hàng</span></a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><x-icon name="user" /> <span>Người dùng</span></a>
            <hr style="width:100%;border:0;border-top:1px solid #2b2b2b;margin:12px 0">
            <a href="{{ route('home') }}"><x-icon name="home" /> <span>Về cửa hàng</span></a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="btn btn-secondary btn-block"><span>Đăng xuất</span></button></form>
        </nav>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="user"><x-admin-notifications /><span>{{ auth()->user()->name }}</span><a href="{{ route('home') }}" class="btn btn-sm btn-secondary">Xem cửa hàng</a></div>
        </header>

        @yield('content')
    </div>

    <x-toast-container />
    <x-confirm-modal />
    @stack('scripts')
</body>
</html>
