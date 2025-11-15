# Ứng dụng Quản lý Công việc Đơn giản (PHP & MySQL)

## 🎯 Mục Tiêu Dự Án
Dự án được xây dựng để thực hành kiến thức PHP thuần, PDO và quản lý cơ sở dữ liệu MySQL, bao gồm các tính năng xác thực người dùng cơ bản và CRUD cho công việc cá nhân.

## 🛠️ Yêu Cầu Kỹ Thuật
* **PHP:** Phiên bản 7.4 trở lên.
* **MySQL:** Phiên bản 5.7 trở lên.
* **Môi trường:** XAMPP / WAMP / MAMP.

## ⚙️ Hướng Dẫn Cài Đặt

1.  **Clone Repository:** Tải mã nguồn về máy cục bộ.
    ```bash
    git clone [https://github.com/vandatpham14/php-simple-todo-list.git](https://github.com/vandatpham14/php-simple-todo-list.git)
    ```

2.  **Cấu hình Database:**
    * Tạo Database mới trong phpMyAdmin với tên là `todo_app`.
    * Chạy mã SQL trong tệp `sql/database.sql` để tạo các bảng `users` và `tasks`.

3.  **Cấu hình Kết nối:**
    * Mở tệp `config/db.php`.
    * Kiểm tra và cập nhật thông tin kết nối nếu cần (đặc biệt là `DB_USER` và `DB_PASS`).
    
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'todo_app');
    define('DB_USER', 'root');
    define('DB_PASS', ''); // Mật khẩu mặc định của XAMPP là rỗng
    ```

4.  **Truy cập:** Truy cập ứng dụng qua trình duyệt: `http://localhost/php-simple-todo-list/public/register.php` (hoặc cổng riêng của bạn).
