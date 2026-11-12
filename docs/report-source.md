# Báo cáo đồ án — Website bán sách trực tuyến Mộc Bản

## 1. Giới thiệu

Mộc Bản là website bán sách trực tuyến viết bằng PHP 8.x theo kiến trúc MVC tự xây dựng. Giao diện tiếng Việt, tiền tệ VNĐ, chạy trên Apache và dùng MySQL qua PDO Prepared Statements.

## 2. Phạm vi chức năng

Khách vãng lai có thể xem sách, danh mục, tìm kiếm và chi tiết sách. Khách hàng có tài khoản có thể quản lý giỏ hàng Session, đặt đơn COD và xem lịch sử đơn. Quản trị viên có thể quản lý sách, danh mục, đơn hàng và khách hàng.

Thanh toán online và email chưa nằm trong phạm vi phát hành hiện tại. Voucher và đánh giá sách đã được tích hợp; biến thể bìa mềm/bìa cứng cần xác nhận nghiệp vụ trước khi triển khai.

## 3. Kiến trúc và công nghệ

- `public/index.php`: front controller và khai báo route.
- `app/Controllers`: điều phối request.
- `app/Models`: truy vấn PDO và transaction.
- `app/Views`: layout, partial và màn hình HTML.
- `app/Core`: Router, View, Session, CSRF, Database.
- Apache `.htaccess`, PHP OOP, MySQL, PDO Prepared Statements, CSS/JavaScript thuần.

## 4. Cơ sở dữ liệu

Các bảng `users`, `categories`, `books`, `orders`, `order_items` được liên kết bằng khóa ngoại. Tồn kho được khóa trong transaction khi tạo đơn; chi tiết đơn lưu snapshot tên sách, giá và số lượng.

## 5. Kiểm thử

53 file PHP đã qua lint. Route shop, route admin sau xác thực, 404, asset và chặn thư mục nội bộ đã được kiểm tra qua HTTP trên Laragon. Smoke test giỏ hàng, CSRF, transaction đơn hàng và rollback đã đạt.

## 6. Hướng dẫn cài đặt

Đặt source trong `C:\laragon\www`, cấu hình Apache DocumentRoot vào thư mục `public`, tạo database `bookstore_web212`, chạy `database/schema.sql` rồi `database/seed.sql`, sau đó mở `http://localhost/`.

Tạo Admin bằng `php tools/create_admin.php`. Không commit mật khẩu hoặc `.env` thật.

Website production đã triển khai tại `https://nhom9.khag.tech/`. Tài khoản Admin production được bàn giao riêng; không ghi mật khẩu trong source.

## 7. Hạn chế và hướng phát triển

Thanh toán online, gửi email, API nghiệp vụ và biến thể sách là các hạng mục mở rộng cần quyết định nghiệp vụ hoặc tích hợp dịch vụ tương ứng. Voucher và đánh giá sách đã được tích hợp trong bản hiện tại.
