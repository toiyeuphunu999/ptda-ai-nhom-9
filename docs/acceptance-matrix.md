# Ma trận kiểm tra hiện trạng — WEB212

Cập nhật: 2026-11-28. Phạm vi kiểm tra trong workspace `C:\laragon\www`.

| Hạng mục | Bằng chứng | Trạng thái |
|---|---|---|
| PHP OOP/MVC | `app/Controllers`, `app/Models`, `app/Views`, `public/index.php` | PASS |
| Routing tập trung | `config/routes.php`, `public/index.php`, `public/.htaccess` | PASS |
| PDO prepared statements | `app/Models/Database.php` và các Model | PASS |
| Authentication/authorization | `AuthPageController`, `Auth`, `AdminController`, `Session` | PASS qua source audit |
| CSRF/session/password hash | `Csrf`, `Session`, form POST và Auth | PASS qua source audit |
| Catalogue và tìm kiếm | `BookController`, `Book`, các view shop | PASS qua source audit |
| Cart/checkout/order | `CartPageController`, `Cart`, `Checkout`, `Order` | PASS qua source audit |
| Voucher/review/wishlist | `Voucher`, `Review`, `Wishlist` và route tương ứng | PASS qua source audit |
| PayOS/SMTP/Google OAuth | `PayOSService`, `SmtpMailer`, `Auth`, system settings | Có code; runtime cần credential |
| Admin dashboard/report/settings | Admin controllers, models và views | PASS qua source audit |
| Responsive/accessibility/UI | Tailwind input, generated CSS, views và JS | Chưa browser-test trong turn này |
| Database schema/migrations | `database/schema.sql`, migrations `002`–`017` | PASS đối chiếu file |
| Documentation | README, SRS, use cases, ERD, deploy, MVC | Đã đồng bộ 2026-11-28 |

## Kiểm tra đã chạy

- PHP lint: **95/95 PASS**.
- JavaScript syntax: **5/5 PASS**.
- `git diff --check`: **PASS**.
- Forbidden syntax audit: không còn `match`, arrow function hoặc constructor property promotion trong source PHP.

## Chưa thể xác nhận trong workspace này

- HTTP route smoke, browser responsive, database E2E, OAuth, SMTP và PayOS webhook chưa chạy trong lần đồng bộ tài liệu này.
- PHP built-in server trước đó bị môi trường từ chối khởi động, vì vậy không ghi nhận các kiểm tra runtime là PASS.
- Không xác nhận live URL hoặc credential production nếu không có bằng chứng chạy mới trong workspace.
