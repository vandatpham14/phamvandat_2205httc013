<?php
// Luôn bắt đầu session trước khi thao tác với nó
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu có cookie session, hãy xóa nó
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit();
?>