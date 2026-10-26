@extends('layouts.app')
@section('title', 'Đăng ký')
@section('content')
<div class="auth-shell">
    <section class="auth-visual"><p class="eyebrow">Bắt đầu hành trình</p><h2>ĐÔI GIÀY ĐÚNG. PHONG CÁCH ĐÚNG.</h2><p>Tạo tài khoản để lưu lựa chọn và theo dõi từng bước của đơn hàng.</p></section>
    <div class="auth-main"><div class="auth-card"><p class="eyebrow">Thành viên mới</p><h1>Tạo tài khoản</h1><p class="auth-card__lead">Chỉ mất một phút để bắt đầu.</p>
        <form method="POST" action="{{ route('register') }}" data-validate novalidate>@csrf
            <div class="form-group"><label for="name">Họ và tên</label><input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="name" required data-required-message="Vui lòng nhập họ tên.">@error('name')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="name" hidden></span></div>
            <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required data-required-message="Vui lòng nhập email." data-email-message="Email không hợp lệ.">@error('email')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="email" hidden></span></div>
            <div class="form-group"><label for="phone">Số điện thoại <small>(không bắt buộc)</small></label><input type="tel" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" autocomplete="tel">@error('phone')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="form-grid"><div class="form-group"><label for="password">Mật khẩu</label><input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="6" autocomplete="new-password" required data-required-message="Vui lòng nhập mật khẩu.">@error('password')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="password" hidden></span></div><div class="form-group"><label for="password_confirmation">Xác nhận mật khẩu</label><input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="6" autocomplete="new-password" required data-required-message="Vui lòng xác nhận mật khẩu."><span class="field-error" data-error-for="password_confirmation" hidden></span></div></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Tạo tài khoản <x-icon name="arrow-right" /></button>
        </form>
        <p class="auth-switch">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div></div>
</div>
@endsection
