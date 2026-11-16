-- Repair customer tier labels that were previously stored with broken UTF-8 conversion.
UPDATE customer_tiers
SET
    name = CASE code
        WHEN 'regular' THEN 'Khách hàng thường'
        WHEN 'vip' THEN 'Khách hàng VIP'
        ELSE name
    END,
    description = CASE code
        WHEN 'regular' THEN 'Khách hàng mới hoặc chưa đạt điều kiện VIP'
        WHEN 'vip' THEN 'Khách hàng có tổng chi tiêu từ 2.000.000 đồng'
        ELSE description
    END
WHERE code IN ('regular', 'vip');
