# ERD — Nhà sách Mộc Bản

## Quan hệ

- `categories` 1:N `books` qua `books.category_id`; RESTRICT khi xóa category.
- `users` 1:N `orders` qua `orders.user_id`; RESTRICT khi xóa user có lịch sử đơn.
- `orders` 1:N `order_items` qua `order_items.order_id`; CASCADE khi xóa order.
- `books` 1:N `order_items` qua `order_items.book_id`; RESTRICT để giữ lịch sử bán hàng.
- `users` 1:N `reviews` qua `reviews.user_id`; CASCADE.
- `books` 1:N `reviews` qua `reviews.book_id`; CASCADE.
- `vouchers` liên hệ logic với `orders.voucher_code`; không dùng FK vì order lưu snapshot.
- `customer_tiers` 1:N `users` qua `users.customer_tier_id`; RESTRICT khi xóa hạng.
- `orders` 1:1 `payments` qua `payments.order_id`; CASCADE khi xóa order.
- `orders` 1:N `order_status_history` qua `order_status_history.order_id`; CASCADE.

## Bảng

### users
`id` PK; `name` VARCHAR(120) NOT NULL; `email` VARCHAR(190) UNIQUE NOT NULL; `password_hash` VARCHAR(255) NOT NULL; `role` ENUM('customer','admin') DEFAULT 'customer'; `customer_tier_id` FK; `created_at`, `updated_at` TIMESTAMP.

### customer_tiers
`id` PK; `code` VARCHAR(30) UNIQUE; `name`; `min_spending`; `discount_percent`; `description`; timestamps. Dùng để phân biệt khách thường và VIP.

### categories
`id` PK; `name` VARCHAR(120) NOT NULL; `slug` VARCHAR(150) UNIQUE NOT NULL; `description` TEXT NULL; timestamps.

### books
`id` PK; `category_id` FK NOT NULL; `title` VARCHAR(255) NOT NULL; `slug` VARCHAR(255) UNIQUE NOT NULL; `author` VARCHAR(180) NOT NULL; `publisher` VARCHAR(180) NULL; `price` INT UNSIGNED NOT NULL; `original_price` INT UNSIGNED NULL; `description` TEXT NULL; `cover` VARCHAR(255) NULL; `stock_quantity` INT UNSIGNED NOT NULL; `status` ENUM('draft','published','hidden') DEFAULT 'draft`; timestamps. Check `original_price IS NULL OR original_price >= price`.

### orders
`id` PK; `user_id` FK NOT NULL; `order_code` VARCHAR(32) UNIQUE NOT NULL; `status` ENUM('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending`; `subtotal`, `shipping_fee`, `discount_amount`, `total` INT UNSIGNED; `voucher_code` VARCHAR(50) NULL; `shipping_name` VARCHAR(120); `shipping_phone` VARCHAR(30); `shipping_address` VARCHAR(255); timestamps.

### payments
`id` PK; `order_id` UNIQUE FK; `method` ENUM('cod','bank_transfer','momo','vnpay','zalopay'); `status` ENUM('pending','processing','paid','failed','cancelled','refunded'); `amount`; `transaction_code`; `provider`; `provider_response`; `paid_at`; timestamps.

### order_status_history
`id` PK; `order_id` FK; `from_status`; `to_status`; `changed_by` FK NULL; `note`; `created_at`. Dùng để truy vết quá trình xử lý đơn.

### order_items
`id` PK; `order_id` FK NOT NULL; `book_id` FK NOT NULL; `book_title` VARCHAR(255) snapshot; `unit_price`, `quantity`, `line_total` INT UNSIGNED NOT NULL.

### vouchers
`id` PK; `code` VARCHAR(50) UNIQUE NOT NULL; `type` ENUM('percent','fixed'); `value`, `min_order_amount`, `used_count` INT UNSIGNED; `starts_at`, `expires_at` DATETIME NULL; `usage_limit` INT UNSIGNED NULL; `status` ENUM('active','inactive'); timestamps.

### reviews
`id` PK; `user_id` FK NOT NULL; `book_id` FK NOT NULL; `rating` TINYINT UNSIGNED 1..5; `comment` TEXT NOT NULL; `status` ENUM('published','hidden'); timestamps; UNIQUE(`user_id`,`book_id`).

## Báo cáo và phân tích

- `v_daily_sales_report`: doanh thu, giảm giá, số đơn và số lượng sách theo ngày.
- `v_book_sales_report`: sách bán chạy theo số lượng và doanh thu.
- `v_customer_sales_report`: tổng đơn, tổng chi tiêu, hạng khách hàng và lần mua gần nhất.
- `v_order_payment_report`: đối chiếu trạng thái đơn và trạng thái thanh toán.

## Bảo toàn lịch sử

`order_items` lưu tên/giá snapshot. `orders` lưu voucher code và discount snapshot. Biến thể sách và địa chỉ riêng chưa thuộc schema hiện tại; thanh toán online đã có cấu trúc `payments` nhưng vẫn cần tích hợp gateway/callback ở tầng ứng dụng.
