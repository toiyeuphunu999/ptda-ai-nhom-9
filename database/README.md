# Database — Nhà sách Mộc Bản

## Khởi tạo database mới

1. Tạo database `bookstore_web212` với `utf8mb4`.
2. Chạy `database/schema.sql`.
3. Chạy `database/seed.sql` nếu cần dữ liệu mẫu local.
4. Tạo admin bằng `php tools/create_admin.php`.

## Nâng cấp database cũ

Chạy các file trong `database/migrations/` theo thứ tự tên:

`002_reviews_vouchers.sql` → `003_real_publishers.sql` → `004_payments_customer_tiers_reports.sql` → `005_system_settings.sql` → `006_repair_sales_reports.sql` → `007_repair_customer_tier_utf8.sql` → `008_member_points.sql` → `009_google_login.sql` → `010_email_verification.sql` → `011_password_reset_tokens.sql` → `012_user_contact_address.sql` → `013_order_shipping_email.sql` → `014_customer_management.sql` → `015_user_addresses.sql` → `016_wishlists.sql` → `017_order_flow_and_book_preview.sql`.

Mỗi migration chỉ chạy một lần trên từng database. Backup trước khi chạy production. Không chạy lại seed trên dữ liệu thật.

## Bảng hiện tại

Schema hiện tại gồm: `users`, `customer_tiers`, `user_addresses`, `system_settings`, `categories`, `books`, `wishlists`, `orders`, `payments`, `order_status_history`, `vouchers`, `reviews`, `order_items`, `password_reset_tokens`, `member_point_transactions` và `wallet_transactions`.

Các truy vấn báo cáo hiện được thực hiện trực tiếp trong `app/Models/Order.php`. Schema/migration có các mẫu view báo cáo được comment để tham khảo, nhưng các view `v_daily_sales_report`, `v_book_sales_report`, `v_customer_sales_report` và `v_order_payment_report` không được tạo tự động.

`orders` và `order_items` lưu snapshot tên/giá/voucher để lịch sử không bị thay đổi khi catalogue cập nhật. `orders.voucher_code` là quan hệ logic, không dùng foreign key tới `vouchers.code`.

## Quy tắc dữ liệu và bảo mật

- Dùng PDO prepared statements ở tầng Model.
- Không lưu plaintext password hoặc credential OAuth/SMTP/PayOS.
- Tiền lưu bằng số nguyên VNĐ.
- Tồn kho được kiểm tra và cập nhật trong transaction khi tạo đơn.
- Customer chỉ đọc dữ liệu thuộc user của mình; admin mới được quản lý dữ liệu toàn hệ thống.
- Không xóa bản ghi làm mất lịch sử đơn hàng; tuân thủ foreign key và chính sách `ON DELETE` trong schema.
- Secret trong `system_settings` được mã hóa bằng `APP_KEY`.
- Không chỉnh sửa migration cũ để “chạy lại”; tạo migration mới nếu schema cần thay đổi.
