# Trạng thái hồ sơ WEB212

Cập nhật: 2026-11-28. Phạm vi cập nhật: source và tài liệu trong workspace hiện tại.

## Đã có

- Mã nguồn PHP OOP/MVC, router tập trung và `.htaccess`.
- Database schema, seed và migrations `002`–`017`.
- Tài khoản, email verification, password reset và Google OAuth flow.
- Cart, checkout, COD/PayOS flow, tồn kho transaction, voucher, review, wishlist và điểm thành viên.
- Lịch sử đơn hàng, quản lý đơn hàng/khách hàng/thanh toán/cài đặt trong Admin.
- Tài liệu SRS, Use Case, Wireframe, ERD, MVC, kiểm tra, triển khai và phân công.
- PHP lint 95/95, JavaScript syntax 5/5 và `git diff --check` đã PASS.

## Chưa thể xác nhận trong local

- HTTP route, database E2E, responsive browser, OAuth, SMTP và PayOS runtime chưa được xác nhận trong lần cập nhật này.
- Credential và live URL production không được ghi trong repository.
- Biến thể bìa mềm/bìa cứng chưa thuộc schema hiện tại.

## Kiểm thử gần nhất

- PHP lint: 95/95 file PASS.
- JavaScript syntax: 5/5 file PASS.
- `git diff --check`: PASS.
- Runtime route và security path: cần chạy lại khi PHP/Apache và database được bật.
