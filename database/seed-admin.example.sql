-- Template tạo tài khoản Admin. Không chạy nguyên file này khi chưa thay placeholder.
-- Tạo hash bằng: php -r "echo password_hash('MAT_KHAU_MOI', PASSWORD_DEFAULT), PHP_EOL;"
-- Sau đó thay REPLACE_WITH_PASSWORD_HASH bằng hash vừa tạo.
INSERT INTO users (name, email, password_hash, role, customer_tier_id)
VALUES (
    'Quản trị viên',
    'admin@example.test',
    'REPLACE_WITH_PASSWORD_HASH',
    'admin',
    (SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1)
);
