ALTER TABLE orders
    ADD COLUMN shipping_email VARCHAR(190) NULL AFTER shipping_name;

UPDATE orders AS orders_table
INNER JOIN users AS users_table ON users_table.id = orders_table.user_id
SET orders_table.shipping_email = users_table.email
WHERE orders_table.shipping_email IS NULL;

ALTER TABLE orders
    MODIFY COLUMN shipping_email VARCHAR(190) NOT NULL;
