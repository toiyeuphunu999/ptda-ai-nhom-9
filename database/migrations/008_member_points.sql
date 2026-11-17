-- Member points and referral program
ALTER TABLE users
    ADD COLUMN referral_code VARCHAR(20) NULL AFTER customer_tier_id,
    ADD COLUMN referred_by INT UNSIGNED NULL AFTER referral_code;

UPDATE users
SET referral_code = CONCAT('MB', LPAD(id, 6, '0'))
WHERE referral_code IS NULL;

ALTER TABLE users
    ADD UNIQUE KEY uq_users_referral_code (referral_code),
    ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;

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
