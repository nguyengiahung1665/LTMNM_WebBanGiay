@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
    <div style="min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 60px 20px;">
        <div
            style="background: #fff; border-radius: 16px; box-shadow: 0 4px 30px rgba(0,0,0,0.08); padding: 40px; width: 100%; max-width: 420px;">
            <h1 style="font-size: 24px; font-weight: 700; text-align: center; margin-bottom: 8px;">Quên mật khẩu</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px; font-size: 14px;">
                Nhập email của bạn, chúng tôi sẽ gửi liên kết đặt lại mật khẩu!
            </p>

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required
                        placeholder="Nhập email của bạn">
                </div>
                <button type="submit" class="btn btn-primary w-full"
                    style="justify-content: center; padding: 12px; font-size: 16px;">Gửi liên kết đặt lại mật khẩu</button>
            </form>

            <p style="text-align: center; margin-top: 20px; font-size: 14px; color: #666;">
                Nhớ mật khẩu? <a href="{{ route('login') }}" style="color: #e94560; text-decoration: none; font-weight: 600;">Đăng
                    nhập</a>
            </p>
        </div>
    </div>
@endsection
