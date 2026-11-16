-- WEB212: mở rộng database theo yêu cầu nghiệp vụ bán sách.
-- Chạy một lần sau 003_real_publishers.sql khi MySQL đã được backup.
-- Không xóa dữ liệu hiện có; các bảng mới dùng để mở rộng chức năng.

CREATE TABLE IF NOT EXISTS customer_tiers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    min_spending INT UNSIGNED NOT NULL DEFAULT 0,
    discount_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_customer_tiers_discount CHECK (discount_percent <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO customer_tiers (code, name, min_spending, discount_percent, description)
VALUES
    ('regular', 'Khách hàng thường', 0, 0, 'Khách hàng mới hoặc chưa đạt điều kiện VIP'),
    ('vip', 'Khách hàng VIP', 2000000, 5, 'Khách hàng có tổng chi tiêu từ 2.000.000 đồng')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    min_spending = VALUES(min_spending),
    discount_percent = VALUES(discount_percent),
    description = VALUES(description);

ALTER TABLE users
    ADD COLUMN customer_tier_id INT UNSIGNED NULL AFTER role;

UPDATE users u
SET customer_tier_id = (
    SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1
)
WHERE customer_tier_id IS NULL;

ALTER TABLE users
    MODIFY customer_tier_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_users_customer_tier FOREIGN KEY (customer_tier_id)
        REFERENCES customer_tiers(id) ON UPDATE CASCADE ON DELETE RESTRICT;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL UNIQUE,
    method ENUM('cod', 'bank_transfer', 'momo', 'vnpay', 'zalopay') NOT NULL DEFAULT 'cod',
    status ENUM('pending', 'processing', 'paid', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    amount INT UNSIGNED NOT NULL DEFAULT 0,
    transaction_code VARCHAR(120) NULL UNIQUE,
    provider VARCHAR(50) NULL,
    provider_response JSON NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT chk_payments_amount CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    from_status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NULL,
    to_status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL,
    changed_by INT UNSIGNED NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_status_history_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_status_history_user FOREIGN KEY (changed_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_order_status_history_order ON order_status_history(order_id, created_at);

/* Báo cáo dùng truy vấn trực tiếp trong app/Models/Order.php.
CREATE OR REPLACE VIEW v_daily_sales_report AS
SELECT
    DATE(created_at) AS sale_date,
    COUNT(*) AS order_count,
    COALESCE(SUM(subtotal), 0) AS gross_revenue,
    COALESCE(SUM(discount_amount), 0) AS total_discount,
    COALESCE(SUM(total), 0) AS net_revenue
FROM orders
WHERE status IN ('confirmed', 'shipping', 'completed')
GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW v_book_sales_report AS
SELECT
    oi.book_id,
    oi.book_title,
    b.author,
    c.name AS category_name,
    SUM(oi.quantity) AS units_sold,
    SUM(oi.line_total) AS revenue
FROM order_items oi
INNER JOIN orders o ON o.id = oi.order_id
INNER JOIN books b ON b.id = oi.book_id
INNER JOIN categories c ON c.id = b.category_id
WHERE o.status IN ('confirmed', 'shipping', 'completed')
GROUP BY oi.book_id, oi.book_title, b.author, c.name;

CREATE OR REPLACE VIEW v_customer_sales_report AS
SELECT
    u.id AS customer_id,
    u.name,
    u.email,
    ct.code AS customer_type,
    ct.name AS customer_tier,
    COUNT(DISTINCT CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.id END) AS order_count,
    COALESCE(SUM(CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.total ELSE 0 END), 0) AS total_spent,
    MAX(o.created_at) AS last_order_at
FROM users u
LEFT JOIN customer_tiers ct ON ct.id = u.customer_tier_id
LEFT JOIN orders o ON o.user_id = u.id
WHERE u.role = 'customer'
GROUP BY u.id, u.name, u.email, ct.code, ct.name;

CREATE OR REPLACE VIEW v_order_payment_report AS
SELECT
    o.id AS order_id,
    o.order_code,
    o.status AS order_status,
    o.total,
    COALESCE(p.method, 'cod') AS payment_method,
    COALESCE(p.status, 'pending') AS payment_status,
    p.transaction_code,
    p.paid_at,
    o.created_at
FROM orders o
LEFT JOIN payments p ON p.order_id = o.id;
*/
