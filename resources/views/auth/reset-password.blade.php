@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')
<div class="auth-shell">
    <section class="auth-visual"><p class="eyebrow">Bảo mật tài khoản</p><h2>MỘT KHỞI ĐẦU AN TOÀN HƠN.</h2><p>Chọn mật khẩu mới có ít nhất 6 ký tự và không chia sẻ với người khác.</p></section>
    <div class="auth-main"><div class="auth-card"><p class="eyebrow">Mật khẩu mới</p><h1>Đặt lại mật khẩu</h1><p class="auth-card__lead">Hoàn tất thông tin để truy cập lại tài khoản.</p>
        <form method="POST" action="{{ route('password.update') }}" data-validate novalidate>@csrf<input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group"><label for="email">Email</label><input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" required data-required-message="Vui lòng nhập email.">@error('email')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="email" hidden></span></div>
            <div class="form-group"><label for="password">Mật khẩu mới</label><input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="6" required data-required-message="Vui lòng nhập mật khẩu mới.">@error('password')<span class="field-error">{{ $message }}</span>@enderror<span class="field-error" data-error-for="password" hidden></span></div>
            <div class="form-group"><label for="password_confirmation">Xác nhận mật khẩu</label><input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="6" required data-required-message="Vui lòng xác nhận mật khẩu."><span class="field-error" data-error-for="password_confirmation" hidden></span></div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Đặt lại mật khẩu <x-icon name="check" /></button>
        </form>
    </div></div>
</div>
@endsection
