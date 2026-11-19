ALTER TABLE users
    ADD COLUMN wallet_balance INT UNSIGNED NOT NULL DEFAULT 0 AFTER address,
    ADD COLUMN purchase_blocked TINYINT(1) NOT NULL DEFAULT 0 AFTER wallet_balance;

CREATE TABLE wallet_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    amount INT NOT NULL,
    balance_after INT UNSIGNED NOT NULL,
    transaction_type ENUM('admin_adjustment', 'order_payment', 'refund') NOT NULL DEFAULT 'admin_adjustment',
    description VARCHAR(255) NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wallet_transactions_user_created (user_id, created_at),
    CONSTRAINT fk_wallet_transactions_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_wallet_transactions_creator FOREIGN KEY (created_by) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
