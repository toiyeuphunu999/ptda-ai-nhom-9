# Kiến trúc MVC

## Sơ đồ luồng

```text
Browser → public/index.php → Router → Controller → View → HTML response
                                      ↓
                                    Model
```

## Thành phần

- `public/index.php`: front controller, autoload đơn giản, khởi tạo database/session/view, đăng ký route và dispatch.
- `config/routes.php`: nơi duy nhất khởi tạo controller và khai báo route GET/POST.
- `app/Controllers/Router.php`: lớp `App\Core\Router`, khớp route tĩnh/tham số và xử lý 404.
- `app/Controllers/Controller.php`: lớp `App\Core\Controller`, base class tối thiểu cho controller.
- `app/Views/View.php`: lớp `App\Core\View`, render view trong layout shop/admin/auth.
- `app/Controllers/`: điều phối request và chọn view.
- `app/Views/`: HTML trình bày, layout và partial tái sử dụng.
- `app/Models/`: truy vấn database, xử lý nghiệp vụ, transaction và các service hiện hữu.
- `app/Views/ViewHelper.php`: escape HTML và định dạng tiền VNĐ.
- `config/`: route/cấu hình không chứa secret thật; secret lấy từ environment hoặc system settings.
- `public/assets/`: tài nguyên người dùng được phép truy cập.

## Nguyên tắc mở rộng

Khi thêm nghiệp vụ, tạo Model trong `app/Models/`, dùng PDO với prepared statements. Controller tiếp nhận request, kiểm tra input/quyền và gọi Model rồi render View; không viết SQL trong Controller. View không truy vấn database. Các hành động POST phải bổ sung validation, CSRF, session và kiểm tra quyền ở server.

## Bảo mật triển khai

Apache DocumentRoot phải là `public/`. Các thư mục `app/`, `config/`, `database/` và `storage/` không được public. Autoloader hiện ánh xạ class theo tên file để tương thích với các lớp nền tảng đang nằm trong thư mục cũ; khi di chuyển file phải cập nhật autoload và test toàn bộ route. Không lưu credential trong Git hoặc JavaScript phía client.
