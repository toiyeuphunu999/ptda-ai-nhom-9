# Nội dung slide bảo vệ — Website bán sách Mộc Bản

## Slide 1 — Đề tài

Website bán sách trực tuyến Mộc Bản — WEB212.

## Slide 2 — Bài toán và mục tiêu

Số hóa việc xem sách, tìm kiếm, quản lý giỏ hàng và đặt đơn; cung cấp khu vực quản trị dễ giải thích và mở rộng.

## Slide 3 — Người dùng

Khách vãng lai, khách hàng có tài khoản và quản trị viên.

## Slide 4 — Chức năng chính

Trang chủ, danh mục, tìm kiếm, chi tiết sách, tài khoản, Google OAuth, giỏ hàng, checkout COD/PayOS, lịch sử đơn, voucher, đánh giá, wishlist, CRUD sách/danh mục và quản lý đơn/khách hàng.

## Slide 5 — Kiến trúc

PHP OOP/MVC: Router → Controller → Model/View; DocumentRoot là `public`; dữ liệu qua PDO Prepared Statements.

## Slide 6 — Cơ sở dữ liệu

`users` → `orders` → `order_items`; `books` thuộc `categories`. Transaction bảo vệ tồn kho khi tạo đơn.

## Slide 7 — Bảo mật

Password hash, session, CSRF cho POST, kiểm tra role ở server, escape HTML và chặn truy cập thư mục nội bộ.

## Slide 8 — Kiểm thử

95/95 PHP lint PASS; 5/5 JavaScript syntax PASS; `git diff --check` PASS. Runtime route/database/OAuth/SMTP/PayOS cần kiểm tra trong môi trường có server và credentials.

## Slide 9 — Trình diễn production

Khi có môi trường demo hợp lệ, đăng nhập Admin, xem dashboard, tạo/sửa sách hoặc danh mục, kiểm tra đơn hàng và thanh toán. Không ghi credential hoặc URL chưa được xác minh vào source.

## Slide 10 — Giới hạn và hướng phát triển

Voucher, đánh giá, địa chỉ, wishlist và điểm thành viên đã tích hợp. PayOS, SMTP và Google OAuth đã có adapter/flow nhưng cần credential và runtime test; biến thể sách chưa thuộc schema.
