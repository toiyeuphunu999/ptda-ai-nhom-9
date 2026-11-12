# Test Report — Production và kiểm thử hệ thống

## Môi trường

- PHP CLI 8.4.26.
- Apache/Laragon local.
- URL kiểm thử: `http://localhost/`.
- Production URL kiểm thử: `https://nhom9.khag.tech/`.
- Thời điểm: 2026-11-04.

## Kết quả

| Nhóm | Kết quả | Bằng chứng |
|---|---|---|
| PHP syntax | PASS | 53/53 file PHP qua `php -l` |
| Route shop/admin | PASS | Route shop trả 200; route admin đã đăng nhập trả 200 |
| 404 | PASS | `/khong-ton-tai` trả HTTP 404 |
| Asset | PASS | CSS, JS, SVG trả HTTP 200 |
| Internal path | PASS | `/app/`, `/config/`, `/storage/`, `/.env` trả 404 |
| Responsive probe | PASS | Playwright probe không có mục Hỏng, không overflow |
| Browser console | NOT RUN | Chưa có yêu cầu kiểm tra console riêng |
| Contrast thủ công | NOT RUN | Chưa đo bằng công cụ WCAG chuyên dụng |
| Performance Core Web Vitals | NOT RUN | Chưa có hosting/đo thực tế |
| PDO connection | PASS | Kết nối MySQL Laragon |
| Seed data | PASS | 8 categories, 40 books |
| CSRF validation | PASS | Form đăng ký và cart |
| Session cart | PASS | Thêm/xóa sản phẩm qua HTTP smoke test |
| Header cart count | PASS | Header hiển thị số lượng Session thực tế sau khi thêm/xóa sách |
| Header account link | PASS | Khách thấy Đăng nhập; Admin thấy liên kết Quản trị |
| Book filters and sorting | PASS | Lọc danh mục/tác giả/giá và sắp xếp mới nhất, giá tăng/giảm qua query URL |
| Order status labels | PASS | Lịch sử đơn hàng hiển thị nhãn trạng thái tiếng Việt |
| SEO metadata | PASS | Trang chi tiết có lang, title, description, canonical, favicon và Open Graph |
| Order transaction | PASS | Tạo order, order item, trừ tồn kho rồi cleanup test |
| Admin authorization | PASS | Route admin chưa đăng nhập trả 403 |
| Admin login smoke | PASS | Đăng nhập CSRF qua HTTP, mở 5 màn hình admin |
| Customer checkout E2E | PASS | Đăng ký → đăng nhập → thêm giỏ → checkout COD → lịch sử đơn; dữ liệu test đã cleanup |
| Logout CSRF | PASS | POST `/logout` có CSRF trả 302; GET `/logout` trả 404 |
| Admin category CRUD HTTP | PASS | Tạo/sửa/xóa danh mục tạm qua form CSRF; database trở về 8 danh mục |
| Wireframe artifacts | PASS | `shop-overview.svg` và `admin-overview.svg` hợp lệ XML |
| Submission artifacts | PASS | DOCX, PDF và PPTX có header/ZIP structure hợp lệ |
| Production deployment smoke | PASS | Public routes, assets, 404, internal paths và Admin login/routes đã kiểm tra trên Live URL |

## Route đã kiểm tra

`/`, `/books`, `/books/nha-gia-kim`, `/categories/van-hoc`, `/cart`, `/checkout`, `/login`, `/register`, `/account`, `/orders`, `/about`, `/contact`, `/admin`, `/admin/books`, `/admin/books/create`, `/admin/books/1/edit`, `/admin/categories`, `/admin/orders`, `/admin/customers`, `/khong-ton-tai`.

## Giới hạn

Đây là báo cáo kiểm thử nền tảng và chức năng cơ bản. Chưa kiểm tra thanh toán online, email, tải cao hoặc đo Core Web Vitals. Mật khẩu Admin production không được lưu trong tài liệu hoặc mã nguồn.
