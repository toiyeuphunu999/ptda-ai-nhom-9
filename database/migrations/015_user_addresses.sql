CREATE TABLE IF NOT EXISTS user_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    label VARCHAR(50) NOT NULL,
    recipient_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address VARCHAR(255) NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_user_addresses_user_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO user_addresses (user_id, label, recipient_name, phone, address, is_default)
SELECT u.id, 'Địa chỉ mặc định', u.name, u.phone, u.address, 1 FROM users u
WHERE u.phone IS NOT NULL AND u.phone <> '' AND u.address IS NOT NULL AND u.address <> ''
AND NOT EXISTS (SELECT 1 FROM user_addresses a WHERE a.user_id = u.id);
