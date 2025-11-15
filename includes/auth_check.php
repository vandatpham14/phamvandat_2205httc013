<?php
// Tệp này giả định rằng db.php (nơi session_start() được gọi) đã được include trước nó.

if (!isset($_SESSION['user_id'])) {
    // Nếu người dùng chưa đăng nhập, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    exit();
}
?>