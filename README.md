# Nhà sách Mộc Bản — WEB212

Website bán sách trực tuyến cho đồ án WEB212, triển khai trên PHP/Apache/MySQL.

## Phạm vi hiện tại

- PHP 8.x, OOP, MVC và routing tập trung tại `public/index.php`.
- MySQL Laragon database `bookstore_web212`, PDO Prepared Statements và schema trong `database/schema.sql`.
- 8 danh mục và 40 sách mẫu trong `database/seed.sql`.
- Đăng ký/đăng nhập, password hash, session cookie, CSRF và bảo vệ admin.
- CRUD sách/danh mục cơ bản, giỏ hàng Session và đặt hàng COD.
- Lọc sách theo danh mục, tác giả, khoảng giá và sắp xếp theo giá hoặc mới nhất.
- Đơn hàng dùng transaction và khóa tồn kho trong lúc tạo đơn.
- Đánh giá và nhận xét chỉ dành cho khách hàng đã mua sách.
- Admin tạo voucher phần trăm/số tiền, đặt đơn tối thiểu, thời hạn và giới hạn lượt dùng.
- Dashboard Admin thống kê doanh thu tổng, theo ngày/tháng, sách bán chạy và đơn hàng gần đây.
- Database có hạng khách hàng thường/VIP, bảng thanh toán online mở rộng, lịch sử trạng thái đơn và các view báo cáo bán hàng.
- Thanh toán hiện hỗ trợ COD; thanh toán online, email, API nghiệp vụ và biến thể sách chưa nằm trong phạm vi hiện tại.
- Tạo tài khoản Admin bằng `php tools/create_admin.php`; không có credential mặc định trong source.

## Chạy local

Đặt DocumentRoot của Apache vào thư mục `public/`. Khi dùng PHP built-in server:

```text
php -S localhost:8000 -t public
```

Sau đó mở `http://localhost:8000/`.

## Route chính

`/`, `/books`, `/books/{slug}`, `/categories/{slug}`, `/search`, `/cart`, `/checkout`, `/login`, `/register`, `/account`, `/orders`, `/about`, `/contact` và nhóm `/admin` (bao gồm `/admin/vouchers`).

Ảnh bìa mặc định được lưu trong tài nguyên của website. Sách, khách hàng và đơn hàng được quản lý trong database của hệ thống.
