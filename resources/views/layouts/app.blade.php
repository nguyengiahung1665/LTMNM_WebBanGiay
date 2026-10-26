<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-user-logged-in" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="app-user-is-admin" content="{{ auth()->user()?->is_admin ? '1' : '0' }}">
    <meta name="description" content="SneakerShop - giày thể thao chính hãng, phong cách hiện đại.">
    <title>@yield('title', config('app.name')) — SneakerShop</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="storefront">
    <a href="#main-content" class="skip-link">Chuyển đến nội dung chính</a>
    <x-toast-container />

    <div class="announcement-bar">
        <div class="container announcement-bar__inner">
            <span>Miễn phí vận chuyển cho đơn từ 500.000₫</span>
            <div class="announcement-links">
                <a href="tel:0987654321"><x-icon name="phone" size="14" /> 0987 654 321</a>
                <a href="mailto:info@webanhang.com"><x-icon name="mail" size="14" /> Hỗ trợ</a>
            </div>
        </div>
    </div>

    <header class="site-header" data-site-header>
        <div class="container site-header__inner">
            <button type="button" class="icon-button menu-toggle" data-mobile-menu-toggle aria-controls="mobile-navigation" aria-expanded="false" aria-label="Mở menu">
                <x-icon name="menu" size="23" />
            </button>

            <a href="{{ route('home') }}" class="brand-logo" aria-label="SneakerShop - Trang chủ">SNEAKER<span>SHOP</span></a>

            <nav class="desktop-nav" aria-label="Điều hướng chính">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Trang chủ</a>
                @foreach($layoutCategories->take(2) as $category)
                    <a href="{{ route('category.products', $category->slug) }}" class="{{ request('category') === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') && !request('sale') ? 'is-active' : '' }}">Sản phẩm</a>
                <div class="nav-dropdown">
                    <button type="button" class="nav-dropdown__trigger" aria-expanded="false">Thương hiệu <x-icon name="chevron-down" size="14" /></button>
                    <div class="nav-dropdown__panel">
                        @forelse($layoutBrands as $brand)
                            <a href="{{ route('brand.products', $brand->slug) }}">{{ $brand->name }}</a>
                        @empty
                            <span>Đang cập nhật</span>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('products.index', ['sale' => 1]) }}" class="nav-sale {{ request('sale') ? 'is-active' : '' }}">Sale</a>
            </nav>

            <div class="header-actions">
                <form action="{{ route('products.index') }}" method="GET" class="header-search" role="search">
                    <label class="sr-only" for="header-search">Tìm kiếm sản phẩm</label>
                    <input id="header-search" type="search" name="search" placeholder="Tìm kiếm" value="{{ request('search') }}">
                    <button type="submit" aria-label="Tìm kiếm"><x-icon name="search" size="19" /></button>
                </form>
                <button type="button" class="icon-button mobile-search-toggle" data-mobile-search-toggle aria-label="Mở tìm kiếm"><x-icon name="search" /></button>
                <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="icon-button" aria-label="Yêu thích">
                    <x-icon name="heart" />
                    @if($layoutWishlistCount > 0)<span class="action-badge">{{ $layoutWishlistCount > 99 ? '99+' : $layoutWishlistCount }}</span>@endif
                </a>
                <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="icon-button" aria-label="Giỏ hàng">
                    <x-icon name="bag" />
                    @if($layoutCartCount > 0)<span class="action-badge">{{ $layoutCartCount > 99 ? '99+' : $layoutCartCount }}</span>@endif
                </a>
                @auth
                    <div class="account-menu">
                        <button type="button" class="account-menu__trigger" aria-expanded="false">
                            <span class="account-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="account-name">{{ auth()->user()->name }}</span>
                            <x-icon name="chevron-down" size="14" />
                        </button>
                        <div class="account-menu__panel">
                            <div class="account-menu__heading"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div>
                            <a href="{{ route('profile.index') }}">Thông tin tài khoản</a>
                            <a href="{{ route('orders.index') }}">Đơn hàng của tôi</a>
                            <a href="{{ route('wishlist.index') }}">Yêu thích</a>
                            @if(auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}">Quản trị cửa hàng</a>@endif
                            <form action="{{ route('logout') }}" method="POST">@csrf<button type="submit">Đăng xuất</button></form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="header-login">Đăng nhập</a>
                @endauth
            </div>
        </div>

        <form action="{{ route('products.index') }}" method="GET" class="mobile-search" data-mobile-search hidden>
            <input type="search" name="search" placeholder="Bạn đang tìm đôi giày nào?" value="{{ request('search') }}">
            <button type="submit"><x-icon name="search" /> Tìm</button>
        </form>

        <div class="mobile-nav" id="mobile-navigation" data-mobile-menu hidden>
            <div class="mobile-nav__head">
                <strong>Danh mục</strong>
                <button type="button" class="icon-button" data-mobile-menu-close aria-label="Đóng menu"><x-icon name="close" /></button>
            </div>
            <nav aria-label="Điều hướng di động">
                <a href="{{ route('home') }}">Trang chủ</a>
                <a href="{{ route('products.index') }}">Tất cả sản phẩm</a>
                @foreach($layoutCategories as $category)<a href="{{ route('category.products', $category->slug) }}">{{ $category->name }}</a>@endforeach
                <a href="{{ route('products.index', ['sale' => 1]) }}" class="nav-sale">Sale</a>
            </nav>
            <div class="mobile-nav__account">
                @auth
                    <a href="{{ route('profile.index') }}">Tài khoản</a>
                    <a href="{{ route('orders.index') }}">Đơn hàng</a>
                @else
                    <a href="{{ route('login') }}">Đăng nhập</a>
                    <a href="{{ route('register') }}">Đăng ký</a>
                @endauth
            </div>
        </div>
        <button type="button" class="mobile-nav-backdrop" data-mobile-menu-close hidden aria-label="Đóng menu"></button>
    </header>

    <main id="main-content">@yield('content')</main>

    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div class="site-footer__brand">
                <a href="{{ route('home') }}" class="brand-logo brand-logo--light">SNEAKER<span>SHOP</span></a>
                <p>Giày thể thao chính hãng cho mọi chuyển động. Chọn đúng đôi, đi đúng chất.</p>
                <div class="footer-trust"><x-icon name="shield" /> Thanh toán an toàn &nbsp; <x-icon name="refresh" /> Đổi trả dễ dàng</div>
            </div>
            <div><h2>Mua sắm</h2><a href="{{ route('products.index') }}">Sản phẩm</a><a href="{{ route('products.index', ['featured' => 1]) }}">Nổi bật</a><a href="{{ route('products.index', ['sale' => 1]) }}">Khuyến mãi</a></div>
            <div><h2>Hỗ trợ</h2><a href="{{ auth()->check() ? route('profile.index') : route('login') }}">Tài khoản</a><a href="{{ auth()->check() ? route('orders.index') : route('login') }}">Theo dõi đơn hàng</a><a href="mailto:info@webanhang.com">Liên hệ</a></div>
            <div><h2>Liên hệ</h2><p>123 Nguyễn Huệ, TP.HCM</p><a href="tel:0987654321">0987 654 321</a><a href="mailto:info@webanhang.com">info@webanhang.com</a><p>08:00–22:00, Thứ 2–CN</p></div>
        </div>
        <div class="container site-footer__bottom"><span>© {{ date('Y') }} SneakerShop.</span><span>Thiết kế cho người yêu chuyển động.</span></div>
    </footer>

    <x-confirm-modal />
    @stack('scripts')
</body>
</html>
