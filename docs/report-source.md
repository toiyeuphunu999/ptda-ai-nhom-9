# Báo cáo đồ án — Website bán sách trực tuyến Mộc Bản

## 1. Giới thiệu

Mộc Bản là website bán sách trực tuyến viết bằng PHP 8.x theo kiến trúc MVC tự xây dựng. Giao diện tiếng Việt, tiền tệ VNĐ, chạy trên Apache và dùng MySQL qua PDO Prepared Statements.

## 2. Phạm vi chức năng

Khách vãng lai có thể xem sách, danh mục, tìm kiếm và chi tiết sách. Khách hàng có tài khoản có thể quản lý giỏ hàng Session, đặt đơn COD và xem lịch sử đơn. Quản trị viên có thể quản lý sách, danh mục, đơn hàng và khách hàng.

Voucher, đánh giá sách, nhiều địa chỉ, điểm thành viên, Google OAuth, SMTP và PayOS đã có phần triển khai trong source. Các tích hợp ngoài cần credential và kiểm tra runtime riêng; biến thể bìa mềm/bìa cứng chưa thuộc schema hiện tại.

## 3. Kiến trúc và công nghệ

- `public/index.php`: front controller và khai báo route.
- `app/Controllers`: điều phối request.
- `app/Models`: truy vấn PDO và transaction.
- `app/Views`: layout, partial và màn hình HTML.
- `config/routes.php`: hợp đồng route và controller.
- Apache `.htaccess`, PHP OOP, MySQL, PDO Prepared Statements, Tailwind CSS build-time và JavaScript first-party.

## 4. Cơ sở dữ liệu

Các bảng `users`, `categories`, `books`, `orders`, `order_items` được liên kết bằng khóa ngoại. Tồn kho được khóa trong transaction khi tạo đơn; chi tiết đơn lưu snapshot tên sách, giá và số lượng.

## 5. Kiểm thử

95 file PHP và 5 file JavaScript đã qua static lint/syntax ở workspace hiện tại. `git diff --check` cũng PASS. HTTP route, database E2E, OAuth, SMTP và PayOS runtime cần được chạy trong môi trường server/database tương ứng; không ghi nhận chúng là PASS nếu chưa chạy.

## 6. Hướng dẫn cài đặt

Đặt source trong `C:\laragon\www`, cấu hình Apache DocumentRoot vào thư mục `public`, tạo database `bookstore_web212`, chạy `database/schema.sql` rồi `database/seed.sql`, sau đó mở `http://localhost/`.

Tạo Admin bằng `php tools/create_admin.php`. Không commit mật khẩu hoặc `.env` thật.

Không ghi live URL hoặc credential production vào tài liệu khi chưa có bằng chứng deploy tương ứng. Tài khoản admin luôn được tạo riêng bằng `php tools/create_admin.php`.

## 7. Hạn chế và hướng phát triển

Gateway thanh toán, SMTP và Google OAuth cần credential/endpoint hợp lệ và test runtime riêng. Biến thể sách chưa thuộc schema. Voucher, đánh giá, wishlist, địa chỉ, điểm thành viên và quản lý khách hàng đã có trong bản hiện tại.
