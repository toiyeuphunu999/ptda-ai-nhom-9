# Prompt cho dbdiagram.io — Nhà sách Mộc Bản

Dựa đúng vào `database/schema.sql`, các migration `database/migrations/002`–`017`, `docs/srs.md` và `docs/erd.md`, hãy tạo **DBML hoàn chỉnh** để dán vào dbdiagram.io cho database hiện tại của website bán sách Mộc Bản.

Yêu cầu:
1. Giữ đúng các bảng hiện tại: users, customer_tiers, user_addresses, system_settings, categories, books, wishlists, orders, order_items, payments, order_status_history, vouchers, reviews, password_reset_tokens, member_point_transactions, wallet_transactions.
2. Dùng MySQL/InnoDB/utf8mb4; khóa chính INT UNSIGNED AUTO_INCREMENT.
3. Khai báo đúng NOT NULL, NULL, DEFAULT, UNIQUE, ENUM và timestamps.
4. Tạo đúng quan hệ:
   - categories.id 1:N books.category_id, delete restrict, update cascade.
   - users.id 1:N orders.user_id, delete restrict, update cascade.
   - orders.id 1:N order_items.order_id, delete cascade, update cascade.
   - books.id 1:N order_items.book_id, delete restrict, update cascade.
   - users.id 1:N reviews.user_id, delete cascade, update cascade.
   - books.id 1:N reviews.book_id, delete cascade, update cascade.
   - customer_tiers.id 1:N users.customer_tier_id, delete restrict, update cascade.
   - orders.id 1:1 payments.order_id, delete cascade, update cascade.
   - orders.id 1:N order_status_history.order_id, delete cascade, update cascade.
   - users.id 1:N order_status_history.changed_by, delete set null, update cascade.
   - users.id 1:N user_addresses.user_id, wishlists.user_id, password_reset_tokens.user_id, member_point_transactions.user_id, wallet_transactions.user_id.
   - users.id 1:N system_settings.updated_by and wallet_transactions.created_by, delete set null, update cascade.
5. Không tạo FK từ orders.voucher_code tới vouchers.code. Đây là quan hệ logic; order lưu snapshot để bảo toàn lịch sử.
6. Thêm unique cho users.email, categories.slug, books.slug, orders.order_code, vouchers.code và reviews(user_id, book_id).
7. Thêm index cho các FK và trường truy vấn phổ biến.
8. Thêm ghi chú giải thích snapshot trong orders/order_items và điều kiện review.
9. Không tạo database view báo cáo nếu chỉ có mẫu SQL comment; báo cáo hiện truy vấn trong `app/Models/Order.php`.
10. Sinh kết quả theo đúng thứ tự:
   A. DBML hoàn chỉnh.
   B. SQL MySQL CREATE TABLE tương ứng.
   C. Bảng giải thích PK/FK/cardinality/on delete.
 D. Các giả định và giới hạn; bảng payments lưu giao dịch, còn gateway/callback do `app/Models/PayOSService.php` tích hợp ở tầng ứng dụng.

Đầu ra phải dùng tên bảng/cột tiếng Anh đúng như tài liệu, không đổi tên và không thêm bảng ngoài phạm vi.
