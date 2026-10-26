@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="auth-shell">
    <section class="auth-visual"><p class="eyebrow">Thành viên SneakerShop</p><h2>TRỞ LẠI VỚI NHỊP CHẠY CỦA BẠN.</h2><p>Lưu sản phẩm yêu thích, theo dõi đơn hàng và hoàn tất mua sắm nhanh hơn.</p></section>
    <div class="auth-main"><div class="auth-card"><p class="eyebrow">Chào mừng trở lại</p><h1>Đăng nhập</h1><p class="auth-card__lead">Nhập thông tin tài khoản của bạn.</p>
        <form method="POST" action="{{ route('login') }}" data-validate novalidate>@csrf
            <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required data-required-message="Vui lòng nhập email." data-email-message="Email không hợp lệ.">@error('email')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="email" hidden></span></div>
            <div class="form-group"><label for="password">Mật khẩu</label><input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required data-required-message="Vui lòng nhập mật khẩu.">@error('password')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="password" hidden></span></div>
            <div class="form-group check-row"><input type="checkbox" name="remember" id="remember"><label for="remember" style="margin:0">Ghi nhớ đăng nhập</label><a href="{{ route('password.request') }}" class="auth-link" style="margin-left:auto">Quên mật khẩu?</a></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Đăng nhập <x-icon name="arrow-right" /></button>
        </form>
        <p class="auth-switch">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
    </div></div>
</div>
@endsection
