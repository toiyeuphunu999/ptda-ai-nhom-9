# Database Documentation

## Thứ tự cài đặt

1. Tạo database `bookstore_web212`.
2. Chạy `database/schema.sql`.
3. Chạy migrations theo thứ tự số: `002_reviews_vouchers.sql`, `003_real_publishers.sql`, `004_payments_customer_tiers_reports.sql`, `005_system_settings.sql`, `006_repair_sales_reports.sql`, `007_repair_customer_tier_utf8.sql`.
4. Chạy `database/seed.sql` ở local mới.
5. Không chạy seed lại trên dữ liệu thật khi chưa kiểm tra duplicate và tồn kho.

## Mô hình hiện tại

Các bảng nghiệp vụ gồm tài khoản, hạng khách hàng, danh mục, sách, đơn hàng, chi tiết đơn, thanh toán, lịch sử trạng thái đơn, voucher và đánh giá. Báo cáo và phân tích dùng truy vấn trực tiếp trong `app/Models/Order.php`, không tạo view trong database.

## Quy tắc an toàn

- Dùng PDO prepared statements.
- Không lưu plaintext password.
- Migration phải idempotent hoặc được chạy một lần có kiểm soát.
- Không xóa book/category/user nếu làm mất lịch sử quan hệ; tuân theo FK.
- Tiền lưu dạng số nguyên VNĐ.
- Thanh toán online được thiết kế qua bảng `payments`; việc kết nối cổng MoMo/VNPay/ZaloPay vẫn cần service và callback riêng trong ứng dụng.
- `customer_tiers` phân biệt khách thường/VIP theo tổng chi tiêu và chính sách ưu đãi.
- Không chạy migration lặp lại; kiểm tra backup và cấu trúc hiện tại trước khi chạy trên production.
