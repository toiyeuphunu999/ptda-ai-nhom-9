# Use Cases — Nhà sách Mộc Bản

## Quy ước actor

- **Guest:** chưa đăng nhập.
- **Customer:** tài khoản có `users.role=customer`.
- **Admin:** tài khoản có `users.role=admin`.

## UC-01 — Duyệt catalogue
- Actor: Guest, Customer, Admin.
- Route: `/`, `/books`, `/categories/{slug}`.
- Kết quả: chỉ hiển thị sách `published`; hỗ trợ danh mục, tác giả, khoảng giá và sắp xếp.
- Ngoại lệ: không có dữ liệu thì hiển thị empty state.

## UC-02 — Xem chi tiết sách
- Actor: mọi actor.
- Route: `/books/{slug}`.
- Kết quả: hiển thị metadata, giá, tồn kho, review publish; customer đủ điều kiện thấy form review và add cart.

## UC-03 — Tìm kiếm
- Actor: mọi actor.
- Route: `/search?q=...`.
- Kết quả: truy vấn PDO theo từ khóa và bộ lọc; không có kết quả phải báo rõ.

## UC-04 — Đăng ký/đăng nhập
- Actor: Guest.
- Route: `/register`, `/login`.
- Quy tắc: email unique, validate dữ liệu, CSRF, hash/verify password; sai thì không tạo session.

## UC-05 — Quản lý giỏ hàng
- Actor: Customer.
- Route: `/cart`, POST add/update/remove.
- Quy tắc: cart nằm trong Session; mỗi thay đổi kiểm tra sách còn bán và tồn kho; POST bắt buộc CSRF.

## UC-06 — Checkout COD
- Actor: Customer.
- Route: `/checkout` GET/POST.
- Quy tắc: tính subtotal, shipping, voucher, total; transaction khóa các book liên quan; tạo order và order_items snapshot; giảm stock; cập nhật voucher usage.
- Ngoại lệ: giỏ rỗng, thiếu tồn kho, voucher không hợp lệ, dữ liệu nhận hàng sai.

## UC-07 — Lịch sử đơn hàng
- Actor: Customer.
- Route: `/orders`.
- Quy tắc: chỉ truy vấn `orders.user_id` bằng user đang đăng nhập.

## UC-08 — Đánh giá sách
- Actor: Customer.
- Route: POST `/books/{id}/reviews`.
- Quy tắc: phải có lịch sử mua sách; rating 1–5; mỗi user/book một review; review có status kiểm duyệt.

## UC-09 — Quản trị sách và danh mục
- Actor: Admin.
- Route: `/admin/books*`, `/admin/categories*`.
- Quy tắc: admin-only, CSRF, validation; không xóa category đang được book tham chiếu.

## UC-10 — Quản lý đơn hàng
- Actor: Admin.
- Route: `/admin/orders`, POST `/admin/orders/{id}/status`.
- Quy tắc: admin xem tất cả đơn và cập nhật trạng thái; không cho customer sửa trạng thái.

## UC-11 — Quản lý khách hàng
- Actor: Admin.
- Route: `/admin/customers`.
- Kết quả: xem danh sách tài khoản, role, thời gian tạo; không hiển thị password hash.

## UC-12 — Quản lý voucher
- Actor: Admin.
- Route: `/admin/vouchers*`.
- Quy tắc: code unique; value, thời hạn, minimum order, usage limit hợp lệ; order lưu snapshot code/discount.

## UC-13 — Thanh toán và đối soát giao dịch
- Actor: Customer, cổng thanh toán, Admin.
- Luồng chính: tạo bản ghi `payments` ở trạng thái `pending` → nhận kết quả gateway → cập nhật trạng thái giao dịch và mã giao dịch.
- Hiện trạng: COD đang hoạt động; database đã sẵn sàng cho chuyển khoản, MoMo, VNPay và ZaloPay. Gateway/callback thật cần tích hợp riêng trước khi bật production.

## UC-14 — Phân hạng khách hàng VIP/thường
- Actor: Hệ thống, Admin.
- Luồng chính: tổng hợp chi tiêu từ đơn không bị hủy → đối chiếu `customer_tiers` → gán hạng khách hàng và áp dụng chính sách ưu đãi.
- Database: `customer_tiers`, `users.customer_tier_id`, `v_customer_sales_report`.

## UC-15 — Báo cáo và phân tích nghiệp vụ bán sách
- Actor: Admin.
- Kết quả: báo cáo doanh thu theo ngày, sách bán chạy, doanh thu theo danh mục, khách hàng VIP/thường và đối soát đơn/thanh toán.
- Database views: `v_daily_sales_report`, `v_book_sales_report`, `v_customer_sales_report`, `v_order_payment_report`.
