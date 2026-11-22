# Kiến trúc MVC

## Sơ đồ luồng

```text
Browser → public/index.php → Router → Controller → View → HTML response
                                      ↓
                                    Model
```

## Thành phần

- `public/index.php`: front controller, autoload đơn giản, đăng ký route GET và dispatch.
- `app/Controllers/Router.php`: khớp route tĩnh/tham số và xử lý 404.
- `app/Controllers/Controller.php`: base class tối thiểu cho kế thừa.
- `app/Views/View.php`: render view trong layout shop/admin.
- `app/Controllers/`: điều phối request và chọn view.
- `app/Views/`: HTML trình bày, layout và partial tái sử dụng.
- `app/Models/`: truy vấn database và xử lý nghiệp vụ.
- `app/Views/ViewHelper.php`: escape HTML và định dạng tiền VNĐ.
- `config/`: cấu hình không chứa secret thật.
- `public/assets/`: tài nguyên người dùng được phép truy cập.

## Nguyên tắc mở rộng

Khi có database, tạo Model thật trong `app/Models/`, dùng PDO với prepared statements. Controller không chứa HTML dài hoặc SQL; View không truy vấn database. Các hành động POST/PUT/PATCH/DELETE phải bổ sung validation, CSRF, session và kiểm tra quyền ở server.

## Bảo mật triển khai

Apache DocumentRoot phải là `public/`. Các thư mục `app/`, `config/`, `database/` và `storage/` không được public. Không lưu credential trong Git hoặc JavaScript phía client.
