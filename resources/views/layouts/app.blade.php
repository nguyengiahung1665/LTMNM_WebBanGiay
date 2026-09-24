<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- [SOCKET] Cờ báo cho resources/js/app.js biết có nên khởi tạo Echo hay không --}}
    <meta name="app-user-logged-in" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="app-user-is-admin" content="{{ auth()->user()?->is_admin ? '1' : '0' }}">
    <title>@yield('title', config('app.name')) - Web Bán Giày</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #1a1a2e;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .top-bar {
            background: #1a1a2e;
            color: #fff;
            padding: 8px 0;
            font-size: 13px;
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar a {
            color: #aaa;
            text-decoration: none;
            margin-left: 20px;
            font-size: 13px;
        }

        .top-bar a:hover {
            color: #fff;
        }

        .main-header {
            background: #fff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            height: 70px;
            gap: 18px;
        }

        .logo {
            flex: 0 0 auto;
            font-size: 24px;
            font-weight: 800;
            text-decoration: none;
            color: #1a1a2e;
            white-space: nowrap;
        }

        .logo span {
            color: #e94560;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            flex: 1 1 auto;
            min-width: 0;
            list-style: none;
            gap: 8px;
            flex-wrap: nowrap;
            overflow: hidden;
        }

        .nav-menu a {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: #e94560;
            color: #fff;
        }

        .header-actions {
            display: flex;
            align-items: center;
            flex: 0 0 auto;
            flex-wrap: nowrap;
            gap: 12px;
            min-width: 0;
        }

        .search-box {
            flex: 0 1 185px;
            min-width: 150px;
            max-width: 185px;
        }

        .header-actions a {
            white-space: nowrap;
            text-decoration: none;
            color: #333;
            position: relative;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
        }

        .header-actions a:hover {
            background: #f0f0f0;
        }

        .cart-badge {
            background: #e94560;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            position: absolute;
            top: -2px;
            right: -4px;
        }

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
            border-radius: 10px;
            min-width: 200px;
            z-index: 100;
            overflow: hidden;
        }

        .user-dropdown:hover .user-dropdown-menu {
            display: block;
        }

        .user-dropdown-menu a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            border-radius: 0;
        }

        .user-dropdown-menu a:hover {
            background: #f5f5f5;
            color: #e94560;
        }

        .user-dropdown-menu .divider {
            border-top: 1px solid #eee;
        }

        /* Search Bar */
        .search-box {
            display: flex;
            align-items: center;
            background: #f0f0f0;
            border-radius: 25px;
            padding: 0 16px;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            padding: 10px 0;
            width: 100%;
            min-width: 0;
            font-size: 14px;
            font-family: inherit;
        }

        .search-box button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #666;
            padding: 8px;
        }

        /* Footer */
        .footer {
            background: #1a1a2e;
            color: #ccc;
            padding: 60px 0 30px;
            margin-top: 60px;
        }

        .footer .container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
        }

        .footer h3 {
            color: #fff;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .footer p,
        .footer a {
            font-size: 14px;
            line-height: 2;
            color: #aaa;
            text-decoration: none;
            display: block;
        }

        .footer a:hover {
            color: #e94560;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid #333;
            font-size: 13px;
            grid-column: 1 / -1;
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #e94560 100%);
            color: #fff;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .hero p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero .btn {
            display: inline-block;
            padding: 14px 36px;
            background: #fff;
            color: #1a1a2e;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .hero .btn:hover {
            transform: translateY(-2px);
        }

        /* Section */
        .section-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title a {
            font-size: 14px;
            font-weight: 500;
            color: #e94560;
            text-decoration: none;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .product-card .badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #e94560;
            color: #fff;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .product-card .badge.featured {
            background: #ff6b35;
        }

        .product-card .image-wrap {
            padding-top: 100%;
            position: relative;
            overflow: hidden;
            background: #f5f5f5;
        }

        .product-card .image-wrap img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .product-card:hover .image-wrap img {
            transform: scale(1.05);
        }

        .product-card .info {
            padding: 16px;
        }

        .product-card .brand-name {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .product-card .product-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card .product-name a {
            color: #1a1a2e;
            text-decoration: none;
        }

        .product-card .product-name a:hover {
            color: #e94560;
        }

        .product-card .price-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .product-card .current-price {
            font-size: 18px;
            font-weight: 700;
            color: #e94560;
        }

        .product-card .old-price {
            font-size: 14px;
            color: #999;
            text-decoration: line-through;
        }

        .product-card .rating {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            font-size: 13px;
            color: #f59e0b;
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary {
            background: #e94560;
            color: #fff;
        }

        .btn-primary:hover {
            background: #d63851;
        }

        .btn-secondary {
            background: #1a1a2e;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #2d2d4a;
        }

        .btn-outline {
            background: none;
            border: 2px solid #e94560;
            color: #e94560;
        }

        .btn-outline:hover {
            background: #e94560;
            color: #fff;
        }

        .btn-sm {
            padding: 6px 16px;
            font-size: 13px;
        }

        .btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
            color: #fff;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #1a1a2e;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        /* Form */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #e94560;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        select.form-control {
            appearance: auto;
        }

        /* Alert */
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fde8e8;
            color: #c53030;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #e8fde8;
            color: #2f855a;
            border: 1px solid #cfc;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            list-style: none;
            gap: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 16px 0;
        }

        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: #e94560;
        }

        .breadcrumb li:not(:last-child)::after {
            content: '›';
            margin-left: 8px;
            color: #ccc;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin: 24px 0 0;
            padding: 0;
            list-style: none;
        }

        .pagination a,
        .pagination span {
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            color: #333;
            background: #fff;
            border: 1px solid #e0e0e0;
        }

        .pagination a:hover {
            border-color: #e94560;
            color: #e94560;
        }

        .pagination .active {
            background: #e94560;
            color: #fff;
            border-color: #e94560;
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 16px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover td {
            background: #fafafa;
        }

        /* Sidebar */
        .sidebar {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .sidebar h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0f0f0;
        }

        .sidebar .filter-group {
            margin-bottom: 20px;
        }

        .sidebar .filter-group h4 {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
            color: #666;
        }

        .sidebar .filter-group a {
            display: block;
            padding: 6px 0;
            font-size: 14px;
            color: #333;
            text-decoration: none;
        }

        .sidebar .filter-group a:hover {
            color: #e94560;
        }

        .sidebar .filter-group a.active {
            color: #e94560;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .main-header .container {
                flex-wrap: wrap;
                height: auto;
                padding: 12px 0;
            }

            .footer .container {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero h1 {
                font-size: 32px;
            }

            .nav-menu {
                display: none;
            }

            .search-box input {
                width: 120px;
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .footer .container {
                grid-template-columns: 1fr;
            }
        }

        /* Utility */
        .text-center {
            text-align: center;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-40 {
            margin-top: 40px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .mb-40 {
            margin-bottom: 40px;
        }

        .flex {
            display: flex;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .gap-10 {
            gap: 10px;
        }

        .gap-20 {
            gap: 20px;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .w-full {
            width: 100%;
        }

        /* Category Card */
        .cat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .cat-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }

        .cat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .cat-card .icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .cat-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .cat-card p {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .cat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Product Detail */
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin: 40px 0;
        }

        .gallery-main {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: #f5f5f5;
        }

        .gallery-main img {
            width: 100%;
            display: block;
        }

        .gallery-thumbs {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .gallery-thumbs img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .gallery-thumbs img.active,
        .gallery-thumbs img:hover {
            border-color: #e94560;
        }

        .detail-info h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .detail-info .brand {
            color: #999;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .detail-info .price {
            font-size: 32px;
            font-weight: 800;
            color: #e94560;
            margin-bottom: 8px;
        }

        .detail-info .old-price {
            font-size: 18px;
            color: #999;
            text-decoration: line-through;
            margin-left: 10px;
        }

        .detail-info .desc {
            color: #666;
            line-height: 1.8;
            margin: 20px 0;
        }

        .size-options,
        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }

        .size-options button,
        .color-options button {
            padding: 8px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: #fff;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .size-options button:hover,
        .size-options button.active,
        .color-options button:hover,
        .color-options button.active {
            border-color: #e94560;
            background: #fff1f2;
            color: #be123c;
            padding-left: 30px;
            position: relative;
        }

        .color-options button {
            width: auto;
            height: auto;
            border-radius: 8px;
            padding: 8px 20px;
        }

        .qty-input {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0;
        }

        .qty-input input {
            width: 60px;
            text-align: center;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
        }

        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
        }

        /* Review */
        .review-card {
            padding: 16px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .review-card:last-child {
            border: none;
        }

        .review-card .user {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .review-card .date {
            font-size: 12px;
            color: #999;
            margin-left: 10px;
        }

        .review-card .stars {
            color: #f59e0b;
            font-size: 14px;
        }

        .review-card .comment {
            color: #666;
            margin-top: 6px;
            line-height: 1.6;
        }

        /* Star Rating */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            gap: 4px;
            justify-content: flex-end;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            font-size: 28px;
            color: #ddd;
            transition: color 0.2s;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input:checked~label {
            color: #f59e0b;
        }

        /* Wishlist Heart */
        .wishlist-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 22px;
            color: #ccc;
            transition: all 0.2s;
            padding: 8px;
        }

        .wishlist-btn:hover {
            color: #e94560;
        }

        .wishlist-btn.active {
            color: #e94560;
        }
    </style>
    @stack('styles')
</head>

<body>
    <x-toast-container />

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <span>Free Ship cho đơn hàng trên 500.000₫</span>
            <div>
                <a href="tel:0987654321">📞 0987 654 321</a>
                <a href="mailto:info@webanhang.com">✉️ info@webanhang.com</a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="/" class="logo">Sneaker<span>Shop</span></a>

            <ul class="nav-menu">
                <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Trang chủ</a></li>
                <li><a href="/san-pham" class="{{ request()->is('san-pham*') ? 'active' : '' }}">Sản phẩm</a></li>

            </ul>

            <div class="header-actions">
                <form action="/san-pham" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Tìm giày..." value="{{ request('search') }}">
                    <button type="submit">🔍</button>
                </form>

                <a href="/yeu-thich">❤️</a>

                <a href="/gio-hang">
                    🛒
                    @php
                        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
                    @endphp
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>

                @auth
                    <div class="user-dropdown">
                        <a href="#">👤 {{ auth()->user()->name }}</a>
                        <div class="user-dropdown-menu">
                            <a href="/thong-tin">Thông tin tài khoản</a>
                            <a href="/don-hang">Đơn hàng của tôi</a>
                            @if(auth()->user()->is_admin)
                                <div class="divider"></div>
                                <a href="/admin">📊 Quản trị Admin</a>
                            @endif
                            <div class="divider"></div>
                            <a href="/dang-xuat"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪 Đăng
                                xuất</a>
                            <form id="logout-form" action="/dang-xuat" method="POST" style="display:none;">@csrf</form>
                        </div>
                    </div>
                @else
                    <a href="/dang-nhap">🔑 Đăng nhập</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div>
                <h3>SneakerShop</h3>
                <p>Cửa hàng giày thể thao uy tín hàng đầu Việt Nam. Chúng tôi cam kết mang đến những sản phẩm chính hãng
                    chất lượng nhất.</p>
            </div>
            <div>
                <h3>Liên hệ</h3>
                <p>📍 123 Nguyễn Huệ, Quận 1, TP.HCM</p>
                <p>📞 0987 654 321</p>
                <p>✉️ info@webanhang.com</p>
                <p>🕐 8:00 - 22:00 (T2-CN)</p>
            </div>
            <div>
                <h3>Danh mục</h3>
                @foreach(\App\Models\Category::active()->get() as $cat)
                    <a href="/danh-muc/{{ $cat->slug }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div>
                <h3>Thương hiệu</h3>
                @foreach(\App\Models\Brand::active()->get() as $brand)
                    <a href="/thuong-hieu/{{ $brand->slug }}">{{ $brand->name }}</a>
                @endforeach
            </div>
            <div class="footer-bottom">
                &copy; {{ date('Y') }} SneakerShop. Tất cả quyền được bảo lưu.
            </div>
        </div>
    </footer>

    <script>
        // Gallery thumbnail switching
        document.querySelectorAll('.gallery-thumbs img').forEach(thumb => {
            thumb.addEventListener('click', function () {
                const main = this.closest('.product-detail')?.querySelector('.gallery-main img');
                if (main) {
                    main.src = this.src;
                    document.querySelectorAll('.gallery-thumbs img').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Size/Color selection
        document.querySelectorAll('.size-options button').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('.size-options')?.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        document.querySelectorAll('.color-options button').forEach(btn => {
            btn.addEventListener('click', function () {
                this.closest('.color-options')?.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
    <x-confirm-modal />
    @stack('scripts')
</body>

</html>
