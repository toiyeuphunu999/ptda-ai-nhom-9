-- WEB212 bookstore schema
-- Dữ liệu khởi tạo nằm trong seed.sql; biến thể sách chưa thuộc mô hình hiện tại.

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

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    email_verified_at DATETIME NULL,
    email_verification_token_hash CHAR(64) NULL,
    email_verification_expires_at DATETIME NULL,
    password_hash VARCHAR(255) NOT NULL,
    google_id VARCHAR(100) NULL UNIQUE,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    customer_tier_id INT UNSIGNED NOT NULL,
    referral_code VARCHAR(20) NULL UNIQUE,
    referred_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_system_settings_user FOREIGN KEY (updated_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    author VARCHAR(180) NOT NULL,
    publisher VARCHAR(180) NULL,
    price INT UNSIGNED NOT NULL DEFAULT 0,
    original_price INT UNSIGNED NULL,
    description TEXT NULL,
    cover VARCHAR(255) NULL,
    stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'hidden') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_books_original_price CHECK (original_price IS NULL OR original_price >= price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    order_code VARCHAR(32) NOT NULL UNIQUE,
    status ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    subtotal INT UNSIGNED NOT NULL DEFAULT 0,
    shipping_fee INT UNSIGNED NOT NULL DEFAULT 0,
    discount_amount INT UNSIGNED NOT NULL DEFAULT 0,
    voucher_code VARCHAR(50) NULL,
    total INT UNSIGNED NOT NULL DEFAULT 0,
    shipping_name VARCHAR(120) NOT NULL,
    shipping_phone VARCHAR(30) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS vouchers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percent', 'fixed') NOT NULL,
    value INT UNSIGNED NOT NULL,
    min_order_amount INT UNSIGNED NOT NULL DEFAULT 0,
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    usage_limit INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('published', 'hidden') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reviews_user_book (user_id, book_id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reviews_book FOREIGN KEY (book_id) REFERENCES books(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    book_title VARCHAR(255) NOT NULL,
    unit_price INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    line_total INT UNSIGNED NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_book FOREIGN KEY (book_id) REFERENCES books(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT fk_users_customer_tier FOREIGN KEY (customer_tier_id)
        REFERENCES customer_tiers(id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE users
    ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;

CREATE INDEX idx_users_email_verification_token
    ON users(email_verification_token_hash);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_tokens_user (user_id),
    INDEX idx_password_reset_tokens_expires (expires_at),
    CONSTRAINT fk_password_reset_tokens_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS member_point_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    points INT NOT NULL,
    transaction_type ENUM('earned', 'spent', 'adjusted') NOT NULL DEFAULT 'earned',
    source_type VARCHAR(30) NOT NULL,
    source_id INT UNSIGNED NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_member_points_source (user_id, source_type, source_id),
    INDEX idx_member_points_user_created (user_id, created_at),
    CONSTRAINT fk_member_points_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_order_status_history_order ON order_status_history(order_id, created_at);

/* Báo cáo dùng truy vấn trực tiếp trong app/Models/Order.php. */
/*
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
