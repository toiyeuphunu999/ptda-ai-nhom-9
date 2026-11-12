-- Chạy một lần trên database hiện hữu trước khi bật Review/Voucher.
ALTER TABLE orders
    ADD COLUMN discount_amount INT UNSIGNED NOT NULL DEFAULT 0 AFTER shipping_fee,
    ADD COLUMN voucher_code VARCHAR(50) NULL AFTER discount_amount;

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
