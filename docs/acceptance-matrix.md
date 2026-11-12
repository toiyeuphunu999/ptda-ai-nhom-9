# Ma trận nghiệm thu WEB212

Cập nhật: 2026-11-04. Môi trường: Laragon local, Apache, PHP 8.4.26, MySQL `bookstore_web212`.

| Yêu cầu | Bằng chứng hiện tại | Trạng thái |
|---|---|---|
| PHP OOP và MVC | `app/Controllers`, `app/Models`, `app/Views`, `app/Core` | PASS |
| Routing tập trung và `.htaccess` | `public/index.php`, `public/.htaccess` | PASS |
| PDO Prepared Statements | `app/Core/Database.php`, các model PDO | PASS |
| 8 danh mục, 40 sách | `database/seed.sql`, database local | PASS |
| Đăng ký/đăng nhập/phân quyền | `AuthPageController`, `AdminPageController`, HTTP smoke test | PASS |
| CSRF/session/password hash | `Csrf.php`, `Session.php`, auth/cart/order forms | PASS |
| CRUD sách/danh mục | Admin controllers/models/views, HTTP CRUD test | PASS |
| Giỏ hàng Session | `CartPageController`, HTTP add/update/remove test | PASS |
| Đặt hàng COD và tồn kho transaction | `Order.php`, order E2E test và cleanup | PASS |
| Quản lý đơn hàng/khách hàng | Admin views/controllers/models | PASS |
| Responsive/accessibility cơ bản | `public/assets/css/app.css`, responsive probe, labels/alt/focus | PASS |
| SEO cơ bản | title, description, canonical, favicon, Open Graph, `robots.txt` | PASS |
| Internal path protection | `/app/`, `/config/`, `/storage/`, `/.env` trả 404 | PASS |
| Hồ sơ SRS/Use Case/Wireframe/ERD/MVC/Test/Deploy | `docs/`, SVG wireframes, DOCX/PDF/PPTX | PASS |
| SQL và mã nguồn đóng gói | `deliverables/*-sql.zip`, source ZIP v10 | PASS |
| Live URL production | `https://nhom9.khag.tech/`, HTTP smoke và Admin login đã PASS | PASS |
| Tên/chữ ký thành viên nhóm | `docs/team-roles.md` còn placeholder, không tự bịa dữ liệu | BLOCKED |
| Voucher | Admin tạo mã phần trăm/số tiền, thời hạn; checkout áp dụng và lưu giảm giá | PASS |
| Đánh giá sách | Người đã mua mới được chấm sao và nhận xét | PASS |
| Dashboard doanh thu | Tổng doanh thu, ngày/tháng, sách bán chạy và đơn gần đây | PASS |
| Thanh toán online/email | Không có credential hoặc đặc tả dịch vụ | NOT IN SCOPE |
| Biến thể sách | Chờ xác nhận nghiệp vụ bìa mềm/bìa cứng | PENDING TEACHER CONFIRMATION |

## Lệnh kiểm thử chính

- `php -l` trên 53 file PHP: PASS.
- HTTP route smoke: shop chính trả `200`, route không tồn tại trả `404`.
- HTTP E2E: đăng ký → đăng nhập → giỏ hàng → checkout COD → lịch sử đơn: PASS.
- Admin HTTP: đăng nhập, CRUD danh mục, dashboard và các màn hình quản trị: PASS.
- Database sau kiểm thử: 8 categories, 40 books, 0 orders test.
