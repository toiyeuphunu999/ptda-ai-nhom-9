# SRS — Nhà sách Mộc Bản (WEB212)

## 1. Phạm vi

Mộc Bản là website bán sách trực tuyến bằng PHP 8.x, OOP, MVC, MySQL/PDO và Apache. Phiên bản hiện tại hỗ trợ catalogue, tài khoản, giỏ hàng Session, đặt hàng COD/PayOS, voucher, đánh giá, wishlist, địa chỉ nhận hàng, điểm thành viên, phân hạng khách hàng và khu vực quản trị.

Google OAuth, SMTP và PayOS đã có flow/adapter ở tầng ứng dụng nhưng cần credential và runtime test theo môi trường. API công khai, AI recommendation và biến thể size/màu/phiên bản sách chưa thuộc phạm vi. Database có cấu trúc thanh toán để lưu phương thức, giao dịch và đối soát.

## 2. Actor và phân quyền

| Actor | Quyền |
|---|---|
| Khách vãng lai | Xem trang chủ, danh mục, sách, tìm kiếm, chi tiết, about/contact; không đặt hàng hoặc đánh giá. |
| Customer | Đăng ký/đăng nhập/đăng xuất, Google OAuth, quản lý session cart, checkout COD/PayOS, địa chỉ, wishlist, điểm, xem tài khoản/lịch sử đơn và đánh giá sách đã mua. |
| Admin | Toàn bộ quyền customer và dashboard; CRUD sách/danh mục, xem khách hàng, quản lý đơn hàng/thanh toán/trạng thái, ví, cài đặt và CRUD voucher. |

Mọi POST phải kiểm tra CSRF và validation. Route `/admin/*` yêu cầu session hợp lệ với `role=admin`; customer không được truy cập. Dữ liệu phải dùng PDO prepared statements và escape khi render HTML.

## 3. Chức năng theo nhóm

### Public
- Trang chủ, danh sách sách, chi tiết sách, danh mục, tìm kiếm/lọc/sắp xếp.
- Hiển thị sách `published`; sách `draft`/`hidden` chỉ được admin quản lý.
- Hiển thị giá, giá gốc, tác giả, nhà xuất bản, tồn kho và đánh giá đã publish.

### Customer
- Đăng ký bằng email duy nhất; mật khẩu lưu bằng `password_hash`.
- Đăng nhập tạo session; đăng xuất hủy session.
- Cart lưu trong Session; add/update/remove kiểm tra sách và tồn kho.
- Checkout yêu cầu đăng nhập, thông tin người nhận, COD hoặc PayOS; tạo order trong transaction và khóa tồn kho.
- Voucher chỉ hợp lệ khi active, đúng thời gian, đạt đơn tối thiểu và chưa vượt usage limit.
- Review yêu cầu đã mua sách; mỗi `(user_id, book_id)` chỉ một review.
- Customer chỉ xem đơn của chính mình.
- Customer được phân loại theo `customer_tiers` thành khách thường/VIP dựa trên chính sách tổng chi tiêu.

### Admin
- Dashboard tổng quan.
- Sách: create/read/update/delete, category, status, giá, tồn kho, cover, publisher.
- Danh mục: create/read/update/delete; không xóa danh mục đang có sách.
- Đơn hàng: xem và cập nhật `pending -> confirmed -> shipping -> completed`; cho phép hủy theo nghiệp vụ.
- Khách hàng: xem danh sách tài khoản và vai trò.
- Voucher: tạo/xem/xóa voucher phần trăm hoặc số tiền, thời hạn, đơn tối thiểu, giới hạn dùng.

## 4. Route và quyền

| Method | Route | Actor | Mục đích |
|---|---|---|---|
| GET | `/`, `/books`, `/books/{slug}`, `/categories/{slug}`, `/search`, `/about`, `/contact` | Public | Catalogue và trang thông tin |
| GET | `/login`, `/register` | Public | Form xác thực |
| POST | `/login`, `/register`, `/logout` | Guest/Customer | Xác thực tài khoản |
| GET | `/cart`, `/checkout`, `/orders`, `/account` | Customer | Giỏ hàng, checkout, tài khoản, lịch sử |
| POST | `/cart/add/{id}`, `/cart/update`, `/cart/remove/{id}`, `/checkout` | Customer | Thao tác giỏ và tạo COD order |
| POST | `/books/{id}/reviews` | Customer | Gửi đánh giá đủ điều kiện |
| GET | `/admin`, `/admin/books`, `/admin/books/create`, `/admin/books/{id}/edit`, `/admin/categories`, `/admin/orders`, `/admin/customers`, `/admin/vouchers` | Admin | Màn hình quản trị |
| POST | `/admin/books*`, `/admin/categories*`, `/admin/orders/{id}/status`, `/admin/vouchers*` | Admin | Ghi dữ liệu quản trị |

## 5. Trạng thái và quy tắc dữ liệu

- Book: `draft`, `published`, `hidden`.
- Order: `pending`, `confirmed`, `shipping`, `delivered`, `completed`, `cancel_requested`, `return_requested`, `return_shipping`, `returned`, `return_rejected`, `cancelled`.
- Voucher: `active`, `inactive`; `type` là `percent` hoặc `fixed`.
- Payment: hỗ trợ mô hình lưu COD, chuyển khoản, MoMo, VNPay và ZaloPay với trạng thái pending/processing/paid/failed/cancelled/refunded.
- `order_status_history` lưu lại mọi lần thay đổi trạng thái đơn để phục vụ kiểm tra và báo cáo.
- `order_items` giữ snapshot `book_title`, `unit_price`, `line_total`; không phụ thuộc giá catalogue hiện tại.
- `orders` giữ snapshot `voucher_code` và `discount_amount`; không tạo FK cứng tới voucher để bảo toàn lịch sử.
- Tiền lưu số nguyên VNĐ. `original_price` nếu có phải lớn hơn hoặc bằng `price`.

## 6. Báo cáo và phân tích nghiệp vụ

- Báo cáo doanh thu, sách bán chạy, khách hàng và đối soát thanh toán được truy vấn trong `app/Models/Order.php`.
- Các tên view báo cáo trong SQL là mẫu comment, không được tạo tự động.

## 7. Phi chức năng

PHP 8.x; MVC và front controller `public/index.php`; MySQL/InnoDB/utf8mb4; PDO prepared statements; CSRF; password hashing; server-side authorization; responsive tối thiểu 320px; DocumentRoot trỏ `public/`; route lỗi trả 404 thật.
