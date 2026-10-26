<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Nike', 'description' => 'Thương hiệu thể thao hàng đầu thế giới từ Mỹ.'],
            ['name' => 'Adidas', 'description' => 'Thương hiệu thể thao Đức nổi tiếng với công nghệ Boost.'],
            ['name' => 'Puma', 'description' => 'Thương hiệu thể thao Đức, phong cách thể thao đường phố.'],
            ['name' => 'Vans', 'description' => 'Giày trượt ván biểu tượng với phong cách cá tính.'],
            ['name' => 'Converse', 'description' => 'Huyền thoại giày canvas Chuck Taylor All Star.'],
            ['name' => 'Jordan', 'description' => 'Dòng giày bóng rổ và lifestyle mang tính biểu tượng.'],
            ['name' => 'New Balance', 'description' => 'Giày chạy bộ và lifestyle nổi bật với sự êm ái, cân bằng.'],
            ['name' => 'Asics', 'description' => 'Thương hiệu Nhật Bản nổi tiếng với công nghệ GEL cho chạy bộ.'],
            ['name' => 'Reebok', 'description' => 'Giày training và lifestyle có thiết kế thể thao cổ điển.'],
            ['name' => 'Under Armour', 'description' => 'Giày hiệu năng cao cho chạy bộ, bóng rổ và tập luyện.'],
            ['name' => 'Skechers', 'description' => 'Giày đi bộ và chạy bộ chú trọng sự thoải mái hằng ngày.'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(['slug' => Str::slug($brand['name'])], [
                'name' => $brand['name'],
                'logo' => null,
                'status' => true,
            ]);
        }
    }
}
