# Hướng dẫn triển khai

## Local với Laragon

1. Đặt mã nguồn trong thư mục Laragon, ví dụ `C:\laragon\www`.
2. Đặt Apache DocumentRoot vào thư mục `public` của dự án.
3. Bật Apache trong Laragon.
4. Mở `http://localhost/`.
5. Kiểm tra route `/`, `/books` và `/khong-ton-tai`.

## Khởi tạo database

Trong MySQL Laragon, tạo database `bookstore_web212`, sau đó chạy lần lượt `database/schema.sql` và `database/seed.sql`. Không chạy lại seed trên production nếu chưa kiểm tra dữ liệu hiện có.

Nếu database đã tồn tại, chạy thêm các migration `database/migrations/012_user_contact_address.sql`, `database/migrations/013_order_shipping_email.sql` và `database/migrations/014_customer_management.sql` để bật thông tin nhận hàng, email trong đơn và quản lý khách hàng.

## Apache Shared Hosting/cPanel

- Nếu hosting cho phép, đặt DocumentRoot vào thư mục `public/`.
- Nếu không đổi được DocumentRoot, phải lập phương án triển khai riêng và kiểm tra lại quyền truy cập trước khi dùng; không tự ý chuyển toàn bộ thư mục nội bộ ra public.
- Bật URL rewriting và giữ `.htaccess` trong `public/`.
- Không upload `.env` thật, credential, log hoặc thư mục dependency không cần thiết.
- Cấu hình PHP 8.x và tạo database riêng ở giai đoạn chức năng.

## Tạo tài khoản Admin

Không có mật khẩu Admin mặc định trong mã nguồn. Sau khi tạo database, chạy lệnh tại thư mục project:

```text
php tools/create_admin.php
```

Nhập tên, email và mật khẩu riêng cho môi trường cần triển khai. Không commit credential vào Git.

## Production đã triển khai

- Live URL: `https://nhom9.khag.tech/`
- Admin production email: `admin@nhom9.com`
- Mật khẩu: bàn giao riêng, không lưu trong repository hoặc tài liệu công khai.
- Kiểm tra HTTP gần nhất: trang chủ, route sách/danh mục/tìm kiếm, asset, 404 và 5 màn hình Admin đều đạt.
- Dashboard Admin đọc doanh thu trực tiếp từ database; đơn `cancelled` không được tính vào doanh thu.

## Cập nhật database sau khi nâng cấp

Nếu database đã tồn tại từ bản trước, chạy một lần `database/migrations/002_reviews_vouchers.sql` trong phpMyAdmin. Migration tạo bảng `reviews`, `vouchers` và bổ sung thông tin giảm giá vào `orders`. Database mới thì chạy `database/schema.sql` từ đầu.

Để cập nhật nhà xuất bản cho 40 sách hiện có, chạy thêm một lần `database/migrations/003_real_publishers.sql`. Không cần chạy lại toàn bộ seed trên production.

## Cấu hình thanh toán PayOS

Ứng dụng hỗ trợ COD và chuyển khoản online qua PayOS/VietQR. Tạo kênh thanh toán tại `https://my.payos.vn`, sau đó khai báo các biến môi trường sau trên máy chủ; không ghi giá trị thật vào Git:

```text
APP_URL=https://ten-mien-cua-ban.vn
PAYOS_CLIENT_ID=...
PAYOS_API_KEY=...
PAYOS_CHECKSUM_KEY=...
PAYOS_API_URL=https://api-merchant.payos.vn
```

Đặt webhook của kênh PayOS tới:

```text
https://ten-mien-cua-ban.vn/payment/payos/webhook
```

Luồng hoạt động: khách chọn PayOS tại `/checkout` → ứng dụng tạo payment link → chuyển tới `checkoutUrl` của PayOS → PayOS gọi webhook đã xác thực chữ ký HMAC-SHA256 → ứng dụng cập nhật `payments` và trạng thái đơn hàng. `returnUrl/cancelUrl` chỉ dùng để hiển thị kết quả cho người dùng; webhook mới là nguồn xác nhận giao dịch chính thức.

Trước khi bật production, kiểm tra PHP có extension `curl`, HTTPS hoạt động, webhook truy cập được từ Internet và chạy thử một giao dịch nhỏ trên kênh PayOS.

Sau khi chạy migration `005_system_settings.sql` và `006_repair_sales_reports.sql`, Admin có thể vào `/admin/settings` để nhập thông tin cửa hàng và PayOS. Cần cấu hình `APP_KEY` trước khi lưu các khóa PayOS; khóa được mã hóa trong bảng `system_settings`.
