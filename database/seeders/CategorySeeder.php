<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Giày Chạy Bộ', 'description' => 'Giày thể thao chuyên dụng cho chạy bộ đường dài và marathon.'],
            ['name' => 'Giày Bóng Rổ', 'description' => 'Giày bóng rổ cao cấp dành cho sân indoor và outdoor.'],
            ['name' => 'Giày Đá Bóng', 'description' => 'Giày đá bóng với công nghệ đinh phù hợp mọi loại sân cỏ.'],
            ['name' => 'Giày Lifestyle', 'description' => 'Giày thời trang thể thao phù hợp cho mọi hoạt động hằng ngày.'],
            ['name' => 'Giày Tập Gym', 'description' => 'Giày tập luyện đa năng cho gym, crossfit và cử tạ.'],
            ['name' => 'Giày Tennis', 'description' => 'Giày tennis chuyên dụng cho sân đất nện và sân cứng.'],
            ['name' => 'Giày Năng Động', 'description' => 'Giày thể thao đa năng cho các hoạt động ngoài trời.'],
            ['name' => 'Giày Skateboarding', 'description' => 'Giày đế bám và thân bền dành cho trượt ván, phong cách đường phố.'],
            ['name' => 'Dép & Sandals', 'description' => 'Dép thể thao và sandals thoải mái cho ngày hè.'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => Str::slug($cat['name'])], [
                'name' => $cat['name'],
                'description' => $cat['description'],
                'image' => null,
                'status' => true,
            ]);
        }
    }
}
