<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- [SOCKET] Cờ báo cho resources/js/app.js biết có nên khởi tạo Echo hay không --}}
    <meta name="app-user-logged-in" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="app-user-is-admin" content="{{ auth()->user()?->is_admin ? '1' : '0' }}">
    <title>@yield('title', 'Dashboard') - Admin SneakerShop</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #1a1a2e;
            color: #fff;
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            padding: 0 20px 20px;
            font-size: 20px;
            border-bottom: 1px solid #2d2d4a;
        }

        .sidebar h2 span {
            color: #e94560;
        }

        .sidebar nav {
            padding: 10px 0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #aaa;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2d2d4a;
            color: #fff;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 24px;
        }

        .topbar {
            background: #fff;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .topbar h1 {
            font-size: 20px;
            font-weight: 700;
        }

        .topbar .user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        .stat-card .label {
            font-size: 13px;
            color: #999;
            margin-bottom: 6px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
        }

        .stat-card .icon {
            font-size: 32px;
        }

        .chart-wrap {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 22px;
            font-weight: 700;
        }

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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
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

        .btn-warning {
            background: #ffc107;
            color: #1a1a2e;
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 16px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #666;
            font-size: 13px;
        }

        tr:hover td {
            background: #fafafa;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-yellow {
            background: #fff3cd;
            color: #856404;
        }

        .badge-blue {
            background: #cce5ff;
            color: #004085;
        }

        .badge-green {
            background: #d4edda;
            color: #155724;
        }

        .badge-red {
            background: #f8d7da;
            color: #721c24;
        }

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

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .gap-10 {
            gap: 10px;
        }

        .w-full {
            width: 100%;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        .text-center {
            text-align: center;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        select.form-control {
            appearance: auto;
        }

        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .sidebar {
                width: 60px;
            }

            .sidebar a span {
                display: none;
            }

            .main-content {
                margin-left: 60px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <aside class="sidebar">
        <h2>Sneaker<span>Shop</span></h2>
        <nav>
            <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">📊 Dashboard</a>
            <a href="/admin/san-pham" class="{{ request()->is('admin/san-pham*') ? 'active' : '' }}">👟 Sản phẩm</a>
            <a href="/admin/danh-muc" class="{{ request()->is('admin/danh-muc*') ? 'active' : '' }}">📁 Danh mục</a>
            <a href="/admin/thuong-hieu" class="{{ request()->is('admin/thuong-hieu*') ? 'active' : '' }}">🏷️ Thương
                hiệu</a>
            <a href="/admin/don-hang" class="{{ request()->is('admin/don-hang*') ? 'active' : '' }}">📦 Đơn hàng</a>
            <a href="/admin/nguoi-dung" class="{{ request()->is('admin/nguoi-dung*') ? 'active' : '' }}">👤 Người
                dùng</a>
            <hr style="border-color: #2d2d4a; margin: 10px 20px;">
            <a href="/">🏠 Về trang chủ</a>
            <a href="/dang-xuat" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">🚪
                Đăng xuất</a>
            <form id="logout-form" action="/dang-xuat" method="POST" style="display:none;">@csrf</form>
        </nav>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="user">
                <x-admin-notifications />
                <span>👤 {{ auth()->user()->name }}</span>
                <a href="/" class="btn btn-sm btn-secondary">Xem trang</a>
            </div>
        </div>

        @if(session('success'))
            <script>
                // Lưu thành công -> xoá dữ liệu nháp đã lưu trên trình duyệt
                if (window.AdminFormDraft) { AdminFormDraft.clearAll(); }
            </script>
        @endif
        @if(session('error'))
        @endif

        @yield('content')
    </div>

    <x-toast-container />
    <x-confirm-modal />
    @stack('scripts')
</body>

</html>
