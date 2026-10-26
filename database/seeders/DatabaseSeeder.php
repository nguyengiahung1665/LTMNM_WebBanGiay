<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Tạo tài khoản Admin
        User::updateOrCreate(['email' => 'admin@webanhang.com'], [
            'name' => 'Quản Trị Viên',
            'email' => 'admin@webanhang.com',
            'phone' => '0987654321',
            'address' => 'Hà Nội, Việt Nam',
            'password' => bcrypt('12345678'),
            'is_admin' => true,
        ]);

        // Tạo tài khoản User mẫu
        User::updateOrCreate(['email' => 'user@webanhang.com'], [
            'name' => 'Nguyễn Văn A',
            'email' => 'user@webanhang.com',
            'phone' => '0912345678',
            'address' => 'Hồ Chí Minh, Việt Nam',
            'password' => bcrypt('12345678'),
            'is_admin' => false,
        ]);

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
