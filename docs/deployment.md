# Hướng dẫn triển khai

## Local với Laragon

1. Đặt project trong `C:\laragon\www`.
2. Trỏ Apache DocumentRoot vào thư mục `public/`.
3. Bật Apache và MySQL trong Laragon.
4. Tạo database `bookstore_web212`.
5. Chạy `database/schema.sql`, sau đó chạy migration `002` đến `017` nếu database là bản nâng cấp.
6. Chạy `database/seed.sql` chỉ trên database local mới nếu cần dữ liệu mẫu.
7. Sao chép `.env.example` thành `.env`, tạo `APP_KEY` đủ dài và điền database credentials.
8. Mở domain Laragon hoặc `http://localhost/`.

Có thể chạy tạm bằng PHP built-in server:

```text
php -S localhost:8000 -t public
```

## Apache/cPanel

- DocumentRoot phải trỏ vào `public/`.
- Bật `mod_rewrite` và giữ `public/.htaccess`.
- Dùng PHP 8.x với PDO MySQL, cURL và OpenSSL.
- Không đưa `app/`, `config/`, `database/` hoặc `storage/` ra public.
- Không upload `.env`, `storage/app.key`, log, cache, credential hoặc runtime upload.
- Kiểm tra quyền ghi tối thiểu cho thư mục `storage/returns` nếu tính năng ảnh đổi trả được bật.

## Tạo tài khoản admin

Không có admin mặc định. Chạy tại thư mục project:

```text
php tools/create_admin.php
```

Nhập thông tin riêng cho môi trường cần triển khai. Không commit mật khẩu.

## Cấu hình tích hợp

Các biến môi trường mẫu nằm trong `.env.example`.

### Google OAuth

Khai báo `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` và `GOOGLE_REDIRECT_URI`. Redirect URI phải trùng tuyệt đối với cấu hình Google Cloud Console, ví dụ:

```text
http://localhost/auth/google/callback
```

### SMTP

Khai báo `MAIL_SMTP_HOST`, `MAIL_SMTP_PORT`, `MAIL_SMTP_USERNAME`, `MAIL_SMTP_PASSWORD`, `MAIL_SMTP_ENCRYPTION`, `MAIL_FROM` và `MAIL_FROM_NAME`. Có thể quản lý các giá trị này tại `/admin/settings` sau khi cấu hình `APP_KEY`.

### PayOS/VietQR

Khai báo `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY`, `PAYOS_API_URL` và `APP_URL`. Webhook production có dạng:

```text
https://ten-mien-cua-ban.vn/payment/payos/webhook
```

Webhook phải truy cập được từ Internet, dùng HTTPS và được PayOS ký hợp lệ. `returnUrl`/`cancelUrl` chỉ phục vụ hiển thị; webhook mới là nguồn xác nhận thanh toán.

## Sau khi deploy

Kiểm tra tối thiểu:

- Trang chủ, sách, danh mục, tìm kiếm và 404.
- Đăng ký/đăng nhập, CSRF và quyền `/admin`.
- Cart, checkout, tạo đơn và tồn kho.
- `/admin/orders`, `/admin/payments`, `/admin/settings`.
- Google callback, SMTP gửi email và PayOS webhook nếu đã cấu hình.
- Không có stack trace, credential hoặc file nội bộ bị lộ qua HTTP.

Không ghi live URL, tài khoản hoặc kết quả production vào tài liệu nếu chưa kiểm tra ở lần deploy tương ứng.
