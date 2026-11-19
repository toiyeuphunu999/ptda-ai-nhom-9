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
- Thành viên tích điểm khi hoàn tất đơn hàng, đánh giá sách và giới thiệu người dùng mới.
- Dashboard Admin thống kê doanh thu tổng, theo ngày/tháng, sách bán chạy và đơn hàng gần đây.
- Database có hạng khách hàng thường/VIP, bảng thanh toán online mở rộng, lịch sử trạng thái đơn và các view báo cáo bán hàng.
- Thanh toán hiện hỗ trợ COD; thanh toán online, email, API nghiệp vụ và biến thể sách chưa nằm trong phạm vi hiện tại.
- Tạo tài khoản Admin bằng `php tools/create_admin.php`; không có credential mặc định trong source.

### Cấu hình đăng nhập Google

1. Tạo OAuth Client ID loại **Web application** trong Google Cloud Console.
2. Thêm đúng redirect URI: `http://localhost/auth/google/callback` (hoặc URL trong `GOOGLE_REDIRECT_URI`).
3. Chạy migration `database/migrations/009_google_login.sql`.
4. Khai báo `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` và `GOOGLE_REDIRECT_URI` trong môi trường chạy PHP.
5. Mở `/login` và chọn **Tiếp tục với Google**.

### Xác thực email

- Tài khoản đăng ký bằng email sẽ nhận link xác thực có hiệu lực 24 giờ.
- Người dùng chưa xác thực sẽ không thể đăng nhập bằng mật khẩu.
- Cấu hình SMTP host, port, username, password và encryption tại `/admin/settings` để gửi email xác thực.
- Người dùng có thể yêu cầu gửi lại email tại trang `/login`.
- SMTP host, port, username, password, encryption và thông tin người gửi có thể cấu hình tại `/admin/settings`.

### Thông tin nhận hàng mặc định

- Người dùng có thể lưu họ tên, số điện thoại và địa chỉ nhận hàng tại `/account`.
- Các thông tin đã lưu được tự điền vào form thanh toán và vẫn có thể chỉnh sửa trước khi đặt từng đơn.
- Checkout yêu cầu email nhận thông báo và lưu email đó cùng đơn hàng.

### Quên mật khẩu

- Người dùng có thể yêu cầu link đặt lại mật khẩu tại `/forgot-password`.
- Link reset có hiệu lực 60 phút và chỉ sử dụng được một lần.
- Token reset chỉ lưu dạng hash trong bảng `password_reset_tokens`.

## Chạy local

Đặt DocumentRoot của Apache vào thư mục `public/`. Khi dùng PHP built-in server:

```text
php -S localhost:8000 -t public
```

Sau đó mở `http://localhost:8000/`.

## Route chính

`/`, `/books`, `/books/{slug}`, `/categories/{slug}`, `/search`, `/cart`, `/checkout`, `/login`, `/register`, `/account`, `/orders`, `/about`, `/contact`, `/faq`, `/guide`, `/shipping`, `/returns`, `/privacy`, `/terms` và nhóm `/admin` (bao gồm `/admin/vouchers`).

Ảnh bìa mặc định được lưu trong tài nguyên của website. Sách, khách hàng và đơn hàng được quản lý trong database của hệ thống.

## Cấu trúc MVC

- `app/Models`: truy vấn PDO, CRUD và transaction cho dữ liệu nghiệp vụ.
- `app/Controllers`: tiếp nhận request, kiểm tra dữ liệu, gọi Model và render View.
- `app/Views`: giao diện PHP/HTML, layout và partial dùng chung; không chứa SQL.
- `app/Core`: Router, Controller cơ sở, View, Session, CSRF và Database.
- `config/routes.php`: danh sách route của ứng dụng.
- `app/Services/PayOSService.php`: adapter nhỏ để gọi API PayOS; không chứa giao diện.

## Chạy project

1. Tạo database `bookstore_web212` trong MySQL.
2. Chạy `database/schema.sql`, các migration theo thứ tự và `database/seed.sql` nếu là môi trường mới.
3. Kiểm tra thông tin database trong biến môi trường hoặc `config/database.php`.
4. Trỏ Apache DocumentRoot vào thư mục `public/`, hoặc chạy `php -S localhost:8000 -t public`.
5. Tạo tài khoản quản trị bằng `php tools/create_admin.php`.
