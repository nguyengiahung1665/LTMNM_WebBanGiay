<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', ['user' => Auth::user(), 'locations' => $this->locations()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'gender' => 'required|in:male,female,other',
            'province_code' => 'required|string|max:10',
            'ward_code' => 'required|string|max:10',
            'address_detail' => 'required|string|max:255',
        ], [
            'name.required' => 'Họ và tên là bắt buộc.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại chưa đúng định dạng.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'province_code.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'ward_code.required' => 'Vui lòng chọn phường/xã.',
            'address_detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        ]);

        $province = collect($this->locations())->firstWhere('code', $data['province_code']);
        $ward = $province ? collect($province['wards'])->firstWhere('code', $data['ward_code']) : null;
        if (! $province || ! $ward) {
            throw ValidationException::withMessages(['ward_code' => ['Phường/xã không thuộc tỉnh/thành phố đã chọn.']]);
        }

        Auth::user()->update(array_merge($data, [
            'province_name' => $province['name'],
            'ward_name' => $ward['name'],
            'address' => implode(', ', [$data['address_detail'], $ward['name'], $province['name']]),
        ]));

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);
        $user = Auth::user();
        if (! Hash::check($data['current_password'], $user->password)) return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.'])->withInput();
        $user->update(['password' => Hash::make($data['new_password'])]);
        return back()->with('success', 'Mật khẩu đã được thay đổi.');
    }

    private function locations(): array
    {
        $path = resource_path('data/vietnam-addresses.json');
        if (! is_file($path)) return [];
        return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
