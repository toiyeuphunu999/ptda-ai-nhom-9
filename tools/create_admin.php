<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Models/Database.php';
require dirname(__DIR__) . '/app/Models/User.php';

use App\Core\Database;
use App\Models\User;

$read = static function (string $label): string {
    fwrite(STDOUT, $label . ': ');
    return trim((string)fgets(STDIN));
};

$name = $read('Tên admin');
$email = $read('Email admin');
$password = $read('Mật khẩu admin (tối thiểu 8 ký tự)');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    fwrite(STDERR, "Dữ liệu không hợp lệ.\n");
    exit(1);
}

$database = new Database(require dirname(__DIR__) . '/config/database.php');
$userModel = new User($database);
if ($userModel->findByEmail($email)) {
    fwrite(STDERR, "Email đã tồn tại.\n");
    exit(1);
}

$pdo = $database->connection();
$statement = $pdo->prepare(
    "INSERT INTO users (name, email, password_hash, role, customer_tier_id)
     VALUES (:name, :email, :password_hash, :role, (SELECT id FROM customer_tiers WHERE code = 'regular' LIMIT 1))"
);
$statement->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'role' => 'admin',
]);

fwrite(STDOUT, "Tạo Admin thành công. Hãy lưu credential ở nơi an toàn.\n");
