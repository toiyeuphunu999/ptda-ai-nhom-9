# Nội dung slide bảo vệ — Website bán sách Mộc Bản

## Slide 1 — Đề tài

Website bán sách trực tuyến Mộc Bản — WEB212.

## Slide 2 — Bài toán và mục tiêu

Số hóa việc xem sách, tìm kiếm, quản lý giỏ hàng và đặt đơn; cung cấp khu vực quản trị dễ giải thích và mở rộng.

## Slide 3 — Người dùng

Khách vãng lai, khách hàng có tài khoản và quản trị viên.

## Slide 4 — Chức năng chính

Trang chủ, danh mục, tìm kiếm, chi tiết sách, đăng ký/đăng nhập, giỏ hàng, checkout COD, lịch sử đơn, CRUD sách/danh mục và quản lý đơn/khách hàng.

## Slide 5 — Kiến trúc

PHP OOP/MVC: Router → Controller → Model/View; DocumentRoot là `public`; dữ liệu qua PDO Prepared Statements.

## Slide 6 — Cơ sở dữ liệu

`users` → `orders` → `order_items`; `books` thuộc `categories`. Transaction bảo vệ tồn kho khi tạo đơn.

## Slide 7 — Bảo mật

Password hash, session, CSRF cho POST, kiểm tra role ở server, escape HTML và chặn truy cập thư mục nội bộ.

## Slide 8 — Kiểm thử

53/53 PHP lint PASS; route smoke PASS; admin login PASS; 404 và security path PASS; dữ liệu test được dọn sạch.

## Slide 9 — Trình diễn production

Mở `https://nhom9.khag.tech/`, đăng nhập Admin production, xem dashboard, tạo/sửa sách hoặc danh mục, kiểm tra đơn hàng.

## Slide 10 — Giới hạn và hướng phát triển

Hosting thật và Live URL đã hoàn tất; voucher và đánh giá sách đã tích hợp. Thanh toán online, email và biến thể sách cần triển khai/xác nhận tiếp.
