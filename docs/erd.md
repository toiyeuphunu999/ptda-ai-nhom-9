# ERD — Nhà sách Mộc Bản

Tài liệu này mô tả schema thực tế trong `database/schema.sql` sau các migration `002`–`017`. Báo cáo hiện chạy bằng truy vấn trong `app/Models/Order.php`; các view báo cáo trong SQL chỉ là mẫu comment, không được tạo tự động.

## Quan hệ chính

- `customer_tiers` 1:N `users` qua `users.customer_tier_id`.
- `users` 1:N `orders` qua `orders.user_id`, giữ lịch sử đơn khi xóa user.
- `users` 1:N `user_addresses`, `wishlists`, `reviews`, `password_reset_tokens`, `member_point_transactions` và `wallet_transactions`.
- `users` tự tham chiếu 1:N qua `users.referred_by`.
- `categories` 1:N `books` qua `books.category_id`.
- `books` 1:N `order_items`, `reviews` và `wishlists`.
- `orders` 1:N `order_items` và `order_status_history`.
- `orders` 1:1 `payments` qua `payments.order_id`.
- `system_settings.updated_by` và `wallet_transactions.created_by` tham chiếu `users.id`.
- `orders.voucher_code` chỉ là quan hệ logic tới `vouchers.code`; không tạo foreign key để bảo toàn snapshot lịch sử.

## Bảng nghiệp vụ

### users và customer_tiers

`users` lưu tài khoản, role, password hash, Google ID, xác thực email, referral, trạng thái mua hàng, ví và hạng khách hàng. `customer_tiers` lưu mã hạng, mức chi tiêu tối thiểu và phần trăm ưu đãi.

### user_addresses

Lưu nhiều địa chỉ theo user: nhãn, người nhận, số điện thoại, địa chỉ và cờ mặc định. Xóa user thì địa chỉ bị xóa theo.

### categories và books

`categories` có `name`, `slug`, `description`. `books` có category, title, slug, author, publisher, price, original price, preview content, cover, stock và status (`draft`, `published`, `hidden`). Category không thể xóa nếu còn book tham chiếu.

### orders và order_items

`orders` lưu mã đơn, trạng thái, subtotal, phí giao, giảm giá, tổng tiền, voucher snapshot và thông tin giao hàng. Trạng thái hiện tại gồm `pending`, `confirmed`, `shipping`, `delivered`, `completed`, `cancel_requested`, `return_requested`, `return_shipping`, `returned`, `return_rejected`, `cancelled`.

`order_items` lưu snapshot `book_title`, `unit_price`, `quantity`, `line_total`; xóa order sẽ xóa item, còn book được giữ để bảo toàn lịch sử bán.

### payments

Một order có tối đa một payment. `method` hỗ trợ `cod`, `bank_transfer`, `momo`, `vnpay`, `zalopay`; `status` hỗ trợ pending, processing, paid, failed, cancelled và refunded. PayOS dùng lớp service ở ứng dụng, không phải bảng riêng.

### order_status_history

Lưu trạng thái trước/sau, người thay đổi, ghi chú và thời điểm. Lịch sử bị xóa theo order; user thực hiện thay đổi có thể bị set null.

### vouchers và reviews

Voucher có loại `percent` hoặc `fixed`, giá trị, đơn tối thiểu, thời hạn, giới hạn lượt dùng, lượt đã dùng và status. Review có rating, comment, status; một user chỉ review một book một lần.

### wishlists, points, wallet và settings

- `wishlists` có unique `(user_id, book_id)`.
- `member_point_transactions` lưu điểm earned/spent/adjusted và nguồn phát sinh.
- `wallet_transactions` lưu điều chỉnh ví, số dư sau giao dịch, loại giao dịch và admin thực hiện.
- `system_settings` lưu cấu hình key/value; secret được mã hóa ở tầng ứng dụng bằng `APP_KEY`.

### Token và xác thực

`password_reset_tokens` lưu token reset dạng hash, thời hạn và thời điểm sử dụng. Token xác thực email nằm trên `users` dưới dạng hash và thời hạn.

## Bảo toàn lịch sử và dữ liệu

- Tiền là số nguyên VNĐ.
- `order_items` không phụ thuộc giá catalogue hiện tại.
- `orders` lưu voucher code và discount snapshot.
- Không dùng seed lại trên production.
- Khi đổi schema, tạo migration mới thay vì sửa migration đã chạy.
