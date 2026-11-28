# Nhà sách Mộc Bản — WEB212

Website bán sách trực tuyến bằng PHP 8.x, MySQL và Apache. Project dùng MVC tự xây dựng, front controller tại `public/index.php`, PDO prepared statements và Tailwind CSS được build ở thời điểm phát triển.

## Trạng thái hiện tại

- Catalogue: trang chủ, sách, danh mục, tìm kiếm, lọc, sắp xếp và chi tiết sách.
- Tài khoản: đăng ký, đăng nhập, đăng xuất, xác thực email, quên/đặt lại mật khẩu và đăng nhập Google OAuth.
- Customer: giỏ hàng Session, checkout, nhiều địa chỉ nhận hàng, danh sách yêu thích, lịch sử đơn và đánh giá sách đủ điều kiện.
- Đơn hàng: COD và PayOS/VietQR, transaction tồn kho, lịch sử trạng thái, voucher và điểm thành viên.
- Admin: dashboard, báo cáo, sách, danh mục, đơn hàng, thanh toán, khách hàng, voucher và cài đặt hệ thống.
- Giao diện: Tailwind utility classes từ `resources/css/input.css`, đầu ra được build tại `public/assets/css/tailwind.generated.css`.

PayOS, Google OAuth và SMTP là các tích hợp có code trong project nhưng chỉ hoạt động khi môi trường chạy đã khai báo credential hợp lệ. Không commit credential thật.

## Yêu cầu

- PHP 8.x với PDO MySQL, cURL và OpenSSL.
- MySQL/MariaDB.
- Apache với `mod_rewrite` hoặc PHP built-in server cho local.
- Node.js/npm chỉ cần khi thay đổi nguồn Tailwind.

## Cài đặt local

1. Tạo database `bookstore_web212`.
2. Chạy `database/schema.sql`.
3. Với database nâng cấp, chạy các migration trong `database/migrations/` theo thứ tự số từ `002` đến `017`.
4. Chạy `database/seed.sql` trên database local mới nếu cần dữ liệu mẫu.
5. Sao chép `.env.example` thành `.env` và điền thông tin database, `APP_KEY` cùng các tích hợp cần dùng.
6. Trỏ Apache DocumentRoot vào thư mục `public/`, hoặc chạy:

```text
php -S localhost:8000 -t public
```

7. Mở `http://localhost:8000/` hoặc domain Laragon tương ứng.
8. Tạo admin bằng:

```text
php tools/create_admin.php
```

Không có tài khoản hoặc mật khẩu admin mặc định trong source.

## Build CSS

```text
npm install
npm run build:css
```

Chỉnh `resources/css/input.css` hoặc class trong view, sau đó build lại. Không sửa trực tiếp `public/assets/css/tailwind.generated.css`.

## Cấu trúc project

```text
app/
  Controllers/   Điều phối request và kiểm tra quyền
  Models/        PDO, nghiệp vụ, transaction và service hiện tại
  Views/         Layout, partial và giao diện PHP/HTML
config/          Route và cấu hình không chứa secret
database/        Schema, seed và migration
docs/            SRS, use case, ERD, triển khai và kiểm thử
public/          Front controller, .htaccess và asset public
resources/css/   Nguồn Tailwind CSS
storage/         Key/runtime data, không public
tools/            Script quản trị local
```

Một số lớp nền tảng vẫn nằm trong thư mục hiện hữu nhưng dùng namespace `App\Core`, `App\Helpers` hoặc `App\Services`. Autoloader trong `public/index.php` ánh xạ theo tên lớp để giữ tương thích với cấu trúc hiện tại; không tự ý di chuyển file nếu chưa cập nhật autoload và route.

## Route chính

Catalogue: `/`, `/books`, `/books/{slug}`, `/categories/{slug}`, `/search`.

Tài khoản: `/login`, `/register`, `/forgot-password`, `/reset-password`, `/verify-email`, `/auth/google`, `/account`, `/wishlist`.

Mua hàng: `/cart`, `/checkout`, `/orders`, `/orders/{id}`, `/payment/payos/return`, `/payment/payos/cancel`, `/payment/payos/webhook`.

Thông tin: `/about`, `/contact`, `/faq`, `/guide`, `/shipping`, `/returns`, `/privacy`, `/terms`.

Admin: `/admin`, `/admin/reports`, `/admin/books`, `/admin/categories`, `/admin/orders`, `/admin/payments`, `/admin/customers`, `/admin/vouchers`, `/admin/settings`.

## Bảo mật

- Mật khẩu dùng `password_hash`/`password_verify`.
- Form ghi dữ liệu dùng CSRF token và server-side validation.
- SQL dùng PDO prepared statements.
- Route admin kiểm tra session và role ở server.
- Output HTML được escape; rich text được lọc bằng helper riêng.
- `public/` là DocumentRoot; không public `app/`, `config/`, `database/` hoặc `storage/`.
- `.env`, key, log, cache, upload runtime và credential không được commit.

## Kiểm tra

```text
php -l <file.php>
node --check <file.js>
git diff --check
```

Khi có database và web server, cần kiểm tra thêm route public/admin, CSRF, checkout, PayOS webhook, OAuth, SMTP và responsive trên các viewport mục tiêu.

## Tài liệu

- `docs/mvc-architecture.md`: luồng MVC và trách nhiệm thư mục.
- `docs/srs.md`: phạm vi và yêu cầu nghiệp vụ.
- `docs/use-cases.md`: use case và quyền truy cập.
- `docs/erd.md`: bảng và quan hệ database hiện tại.
- `docs/deployment.md`: triển khai local/Apache và tích hợp.
- `database/README.md`: thứ tự schema, migration và quy tắc dữ liệu.
