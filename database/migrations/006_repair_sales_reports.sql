-- Phục hồi các view báo cáo nếu database cũ đã chạy migration 004 chưa đầy đủ.
CREATE OR REPLACE VIEW v_daily_sales_report AS
SELECT DATE(created_at) AS sale_date, COUNT(*) AS order_count,
       COALESCE(SUM(subtotal), 0) AS gross_revenue,
       COALESCE(SUM(discount_amount), 0) AS total_discount,
       COALESCE(SUM(total), 0) AS net_revenue
FROM orders
WHERE status IN ('confirmed', 'shipping', 'completed')
GROUP BY DATE(created_at);

CREATE OR REPLACE VIEW v_book_sales_report AS
SELECT oi.book_id, oi.book_title, b.author, c.name AS category_name,
       SUM(oi.quantity) AS units_sold, SUM(oi.line_total) AS revenue
FROM order_items oi
INNER JOIN orders o ON o.id = oi.order_id
INNER JOIN books b ON b.id = oi.book_id
INNER JOIN categories c ON c.id = b.category_id
WHERE o.status IN ('confirmed', 'shipping', 'completed')
GROUP BY oi.book_id, oi.book_title, b.author, c.name;

CREATE OR REPLACE VIEW v_customer_sales_report AS
SELECT u.id AS customer_id, u.name, u.email,
       ct.code AS customer_type, ct.name AS customer_tier,
       COUNT(DISTINCT CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.id END) AS order_count,
       COALESCE(SUM(CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.total ELSE 0 END), 0) AS total_spent,
       MAX(CASE WHEN o.status IN ('confirmed', 'shipping', 'completed') THEN o.created_at END) AS last_order_at
FROM users u
LEFT JOIN customer_tiers ct ON ct.id = u.customer_tier_id
LEFT JOIN orders o ON o.user_id = u.id
WHERE u.role = 'customer'
GROUP BY u.id, u.name, u.email, ct.code, ct.name;

CREATE OR REPLACE VIEW v_order_payment_report AS
SELECT o.id AS order_id, o.order_code, o.status AS order_status, o.total,
       COALESCE(p.method, 'cod') AS payment_method,
       COALESCE(p.status, 'pending') AS payment_status,
       p.transaction_code, p.paid_at, o.created_at
FROM orders o
LEFT JOIN payments p ON p.order_id = o.id;
