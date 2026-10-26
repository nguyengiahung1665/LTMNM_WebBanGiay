@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')
<div class="auth-shell">
    <section class="auth-visual"><p class="eyebrow">Khôi phục tài khoản</p><h2>QUAY LẠI ĐƯỜNG CHẠY.</h2><p>Chúng tôi sẽ gửi liên kết đặt lại mật khẩu đến đúng email tài khoản của bạn.</p></section>
    <div class="auth-main"><div class="auth-card"><p class="eyebrow">Hỗ trợ đăng nhập</p><h1>Quên mật khẩu?</h1><p class="auth-card__lead">Nhập email đã đăng ký để nhận liên kết khôi phục.</p>
        <form method="POST" action="{{ route('password.email') }}" data-validate novalidate>@csrf
            <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="email" required data-required-message="Vui lòng nhập email." data-email-message="Email không hợp lệ.">@error('email')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="email" hidden></span></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Gửi liên kết <x-icon name="mail" /></button>
        </form>
        <p class="auth-switch"><a href="{{ route('login') }}">← Quay lại đăng nhập</a></p>
    </div></div>
</div>
@endsection
