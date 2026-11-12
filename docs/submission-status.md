# Trạng thái hồ sơ WEB212

Cập nhật: 2026-11-04. Môi trường kiểm tra: Laragon, Apache, PHP 8.4.26, MySQL local.

## Đã có

- Mã nguồn PHP OOP/MVC, router tập trung và `.htaccess`.
- Database SQL gồm schema và seed; 8 danh mục, 40 sách.
- Đăng ký/đăng nhập, session, CSRF và phân quyền khách hàng/quản trị viên.
- CRUD sách/danh mục, giỏ hàng Session, đặt hàng COD, tồn kho transaction.
- Lịch sử đơn hàng, quản lý đơn hàng và khách hàng trong Admin.
- Tài liệu SRS, Use Case, Wireframe, ERD, MVC, Test Report, Deployment và phân công.
- SQL nguồn: `database/schema.sql`, `database/seed.sql`.
- Hồ sơ đã xuất: `deliverables/bao-cao-web212-moc-ban.docx`, `.pdf`, `slide-bao-ve-web212-moc-ban.pptx`.
- Ma trận nghiệm thu: `docs/acceptance-matrix.md`.

## Chưa thể xác nhận trong local

- Live URL và hosting thật: đã triển khai tại `https://nhom9.khag.tech/` và đã kiểm tra HTTP.
- Voucher và đánh giá sách: đã triển khai ở bản hiện tại. Thanh toán online, email và biến thể sách: chưa triển khai; biến thể đang `PENDING TEACHER CONFIRMATION`.
- PDF/DOCX/PPTX hồ sơ local: đã xuất trong thư mục `deliverables/` từ nội dung đã kiểm chứng.
- Tài khoản Admin production đã có; email được ghi trong `docs/deployment.md`, mật khẩu không lưu trong repository.

## Kiểm thử gần nhất

- PHP lint: 53/53 file PASS.
- Route public: `/`, `/books`, `/categories/van-hoc`, `/cart`, `/login` trả 200.
- Route không tồn tại trả 404.
- Admin sau đăng nhập: dashboard, sách, danh mục, đơn hàng, khách hàng đều trả 200.
- `/app/`, `/config/`, `/storage/`, `/.env` đều trả 404.
