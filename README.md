👟 HỆ THỐNG WEB BÁN GIÀY TRỰC TUYẾN (SneakerShop)
Học Phần: Lập Trình Mã Nguồn Mở Với PHP
Giảng Viên Hướng Dẫn: Nguyễn Minh Hiển
Sinh Viên Thực Hiện: 
 - Nguyễn Gia Hưng
 - Nguyễn Thị Thuý Lành
 - Nguyễn Hoà Hưng
  

📌 GIỚI THIỆU ĐỀ TÀI
SneakerShop là hệ thống website bán giày trực tuyến xây dựng trên Laravel, mô phỏng đầy đủ nghiệp vụ một sàn thương mại điện tử quy mô nhỏ: quản lý sản phẩm theo biến thể (size + màu + tồn kho riêng từng biến thể), giỏ hàng, đặt hàng, quản trị đơn hàng, cùng các kỹ thuật tối ưu hệ thống nâng cao (Cache, Queue, WebSocket/Broadcasting).

🌟 TÍNH NĂNG NỔI BẬT

🛍️ 1. Phân Hệ Khách Hàng
- Trang chủ: sản phẩm nổi bật, sản phẩm mới nhất, danh mục, thương hiệu (có Cache).
- Danh sách & chi tiết sản phẩm: lọc theo danh mục/thương hiệu/giá, tìm kiếm, sắp xếp, phân trang.
- Biến thể sản phẩm (Product Variant): mỗi sản phẩm có nhiều biến thể size + màu, mỗi biến thể quản lý tồn kho riêng — khách chọn đúng size/màu còn hàng mới thêm được vào giỏ.
- Giỏ hàng: thêm/sửa số lượng/xóa sản phẩm, kiểm tra tồn kho theo biến thể trước khi cho thêm vào giỏ.
- Đặt hàng (Checkout): giữ chỗ tồn kho ngay lúc đặt hàng (atomic, chống oversell khi nhiều người mua cùng lúc), tính phí vận chuyển tự động, sinh mã đơn hàng.
- Theo dõi đơn hàng: xem lịch sử & chi tiết đơn, trạng thái đơn hàng cập nhật realtime không cần tải lại trang (WebSocket).
- Đánh giá sản phẩm (Review) & Sản phẩm yêu thích (Wishlist).
- Quản lý tài khoản cá nhân, đổi mật khẩu.
- Nhận email xác nhận đơn hàng & email thông báo đổi trạng thái đơn (gửi nền qua hàng đợi, không làm chậm thao tác của khách).

🛡️ 2. Phân Hệ Quản Trị Viên (Admin)
- Dashboard thống kê: tổng doanh thu, tổng đơn hàng, tổng sản phẩm, tổng người dùng, biểu đồ doanh thu 7 ngày gần nhất (Chart.js), thống kê đơn hàng theo trạng thái, danh sách đơn hàng gần đây.
- Quản lý sản phẩm (CRUD): thông tin sản phẩm, ảnh, danh mục, thương hiệu, và quản lý biến thể (size/màu/tồn kho) ngay trong form thêm/sửa sản phẩm.
- Quản lý danh mục & thương hiệu (CRUD).
- Quản lý đơn hàng: xem chi tiết, cập nhật trạng thái đơn (pending → processing → completed / cancelled). Hệ thống tự động trừ/hoàn kho tương ứng khi đơn bị hủy hoặc được khôi phục, có thông báo realtime khi phát sinh đơn hàng mới.
- Quản lý người dùng.

⚙️ 3. Nâng Cao: Cache – Queue – Socket
- Cache: cache danh mục, thương hiệu, danh sách sản phẩm (theo bộ lọc), chi tiết sản phẩm, trang chủ — tự động xóa cache khi admin cập nhật dữ liệu liên quan, giảm đáng kể số lượt truy vấn CSDL lặp lại.
- Queue: gửi email xác nhận đơn hàng và email đổi trạng thái đơn chạy nền qua hàng đợi (`php artisan queue:work`), không chặn phản hồi của người dùng/admin.
- Socket (Broadcasting - Laravel Reverb): admin nhận thông báo đơn hàng mới realtime; khách hàng thấy trạng thái đơn hàng của mình tự cập nhật ngay khi admin thay đổi, không cần F5.

🎨 GIAO DIỆN
- Giao diện khách hàng: theme màu chủ đạo đỏ hồng (#e94560), thiết kế hiện đại, responsive.
- Giao diện quản trị: sidebar tối, dashboard trực quan với biểu đồ Chart.js, bảng dữ liệu rõ ràng có phân trang.

🛠️ CÔNG NGHỆ SỬ DỤNG
- Backend Framework: PHP 8.3+, Laravel 13.x
- Database: SQLite (mặc định môi trường dev) / hỗ trợ MySQL
- Frontend: Blade Template, CSS viết tay tùy biến, Chart.js (dashboard admin)
- Realtime: Laravel Reverb + Laravel Echo (WebSocket)
- Queue: Laravel Queue (driver database)
- Cache: Laravel Cache (driver file/database)
- Build tool: Vite

🚀 HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN

1️⃣ Clone Repository
```
git clone [Điền link repo GitHub của bạn]
cd Do-An-PHP-WebBanGiay
```

2️⃣ Cài Đặt Thư Viện
```
composer install
npm install
```

3️⃣ Cấu Hình File Môi Trường (.env)
```
copy .env.example .env
php artisan key:generate
```
Mặc định dự án dùng SQLite, không cần cấu hình thêm. Nếu muốn dùng MySQL, sửa trong `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webbangiay
DB_USERNAME=root
DB_PASSWORD=
```

4️⃣ Khởi Tạo CSDL & Seed Dữ Liệu Mẫu
```
php artisan migrate --seed
```

5️⃣ Tạo Liên Kết Storage & Build Frontend
```
php artisan storage:link
npm run build
```

6️⃣ Khởi Động Server & Các Tiến Trình Nền
```
php artisan serve
php artisan queue:work
```
```
composer require laravel/reverb
php artisan reverb:install
php artisan reverb:start
```

Truy cập hệ thống tại: http://127.0.0.1:8000

🔑 TÀI KHOẢN ĐĂNG NHẬP MẪU (Sau khi Seed)

| Vai trò | Email | Mật khẩu | Quyền hạn |
|---|---|---|---|
| Admin | admin@webanhang.com | 12345678 | Quản trị toàn bộ hệ thống |
| Khách hàng | user@webanhang.com | 12345678 | Mua hàng, đặt hàng, đánh giá |

📁 CẤU TRÚC THƯ MỤC DỰ ÁN
```
Do-An-PHP-WebBanGiay/
├── app/
│   ├── Events/                # Sự kiện realtime (NewOrderPlaced, OrderStatusUpdated)
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/         # DashboardController, ProductController, CategoryController,
│   │       │                  # BrandController, OrderController, UserController
│   │       ├── HomeController, ProductController, CartController,
│   │       ├── CheckoutController, OrderController, ReviewController,
│   │       └── WishlistController, ProfileController, AuthController
│   ├── Mail/                  # OrderPlacedMail, OrderStatusUpdatedMail (gửi qua Queue)
│   ├── Models/                # Product, ProductVariant, Category, Brand, Order,
│   │                          # OrderItem, CartItem, Review, Wishlist, User
│   └── Services/               # CacheService
├── database/
│   ├── migrations/            # Cấu trúc các bảng CSDL
│   └── seeders/                # Dữ liệu mẫu (Category, Brand, Product)
├── resources/
│   ├── views/                  # Giao diện Blade (khách hàng & admin)
│   └── js/app.js                # Cấu hình Laravel Echo (Socket)
├── routes/
│   ├── web.php                  # Route khách hàng & admin
│   └── channels.php             # Phân quyền kênh Broadcasting (Socket)
└── config/
    ├── cache.php                # Whitelist class được phép cache
    └── broadcasting.php         # Cấu hình Reverb
```

