@extends('layouts.app')

@section('title', 'Thông tin tài khoản')

@section('content')
<div class="container" style="margin-top:30px;min-height:60vh">
    <ul class="breadcrumb"><li><a href="/">Trang chủ</a></li><li>Thông tin tài khoản</li></ul>
    <h1 style="font-size:28px;font-weight:700;margin-bottom:26px">Thông tin tài khoản</h1>

    @php $passwordTab = $errors->hasAny(['current_password', 'new_password', 'new_password_confirmation']); @endphp
    <div class="profile-shell" data-profile-tabs>
        <nav class="profile-menu" aria-label="Cài đặt tài khoản">
            <button type="button" class="profile-tab {{ $passwordTab ? '' : 'is-active' }}" data-profile-tab="personal">Cập nhật thông tin cá nhân</button>
            <button type="button" class="profile-tab {{ $passwordTab ? 'is-active' : '' }}" data-profile-tab="password">Đổi mật khẩu</button>
        </nav>

        <div class="profile-panel">
            <section class="profile-section" data-profile-section="personal" {{ $passwordTab ? 'hidden' : '' }}>
                <h2 style="font-size:20px;margin-bottom:20px">Thông tin cá nhân</h2>
                <form method="POST" action="/thong-tin" data-validate novalidate>
                    @csrf
                    <div class="form-grid">
                        <div class="form-group span-2">
                            <label for="name">Họ và tên *</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required data-required-message="Họ và tên là bắt buộc." aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                            @error('name')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="name" hidden></span>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled style="background:#f5f5f5">
                        </div>
                        <div class="form-group">
                            <label for="phone">Số điện thoại *</label>
                            <input id="phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required data-required-message="Vui lòng nhập số điện thoại.">
                            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="phone" hidden></span>
                        </div>
                        <div class="form-group span-2">
                            <label>Giới tính *</label>
                            <div style="display:flex;gap:20px;flex-wrap:wrap;padding-top:8px">
                                @foreach(['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Không xác định'] as $value => $label)
                                    <label><input type="radio" name="gender" value="{{ $value }}" {{ old('gender', $user->gender ?? 'other') === $value ? 'checked' : '' }}> {{ $label }}</label>
                                @endforeach
                            </div>
                            @error('gender')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="province_code">Tỉnh / Thành phố *</label>
                            <select id="province_code" name="province_code" class="form-control @error('province_code') is-invalid @enderror" required data-selected="{{ old('province_code', $user->province_code) }}" data-required-message="Vui lòng chọn tỉnh/thành phố.">
                                <option value="">Chọn tỉnh/thành phố</option>
                            </select>
                            @error('province_code')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="province_code" hidden></span>
                        </div>
                        <div class="form-group">
                            <label for="ward_code">Phường / Xã *</label>
                            <select id="ward_code" name="ward_code" class="form-control @error('ward_code') is-invalid @enderror" required data-selected="{{ old('ward_code', $user->ward_code) }}" data-required-message="Vui lòng chọn phường/xã." disabled>
                                <option value="">Chọn phường/xã</option>
                            </select>
                            @error('ward_code')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="ward_code" hidden></span>
                        </div>
                        <div class="form-group span-2">
                            <label for="address_detail">Địa chỉ chi tiết *</label>
                            <input id="address_detail" type="text" name="address_detail" class="form-control @error('address_detail') is-invalid @enderror" value="{{ old('address_detail', $user->address_detail) }}" placeholder="Số nhà, tên đường..." required data-required-message="Vui lòng nhập địa chỉ chi tiết.">
                            @error('address_detail')<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="address_detail" hidden></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu thông tin</button>
                </form>
            </section>

            <section class="profile-section" data-profile-section="password" {{ $passwordTab ? '' : 'hidden' }}>
                <h2 style="font-size:20px;margin-bottom:20px">Đổi mật khẩu</h2>
                <form method="POST" action="/thong-tin/doi-mat-khau" data-validate novalidate>
                    @csrf
                    @foreach([
                        ['current_password','Mật khẩu hiện tại','Vui lòng nhập mật khẩu hiện tại.'],
                        ['new_password','Mật khẩu mới','Vui lòng nhập mật khẩu mới.'],
                        ['new_password_confirmation','Xác nhận mật khẩu mới','Vui lòng xác nhận mật khẩu mới.']
                    ] as [$field,$label,$requiredMessage])
                        <div class="form-group">
                            <label for="{{ $field }}">{{ $label }} *</label>
                            <input id="{{ $field }}" type="password" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" required {{ $field === 'new_password' ? 'minlength=6' : '' }} data-required-message="{{ $requiredMessage }}">
                            @error($field)<span class="field-error">{{ $message }}</span>@enderror
                            <span class="field-error" data-error-for="{{ $field }}" hidden></span>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                </form>
            </section>
        </div>
    </div>
</div>
<script id="vietnam-addresses-data" type="application/json">{!! json_encode($locations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-profile-tab]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('[data-profile-tab]').forEach(tab => tab.classList.toggle('is-active', tab === button));
    document.querySelectorAll('[data-profile-section]').forEach(section => section.hidden = section.dataset.profileSection !== button.dataset.profileTab);
}));
</script>
@endpush
