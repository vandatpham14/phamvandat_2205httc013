<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Đảm bảo đã đăng nhập

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    redirect('index.php');
}

try {
    // Bảo mật tối quan trọng:
    // Đảm bảo người dùng chỉ có thể XÓA công việc của CHÍNH HỌ
    // bằng cách thêm `AND user_id = :user_id` vào câu lệnh DELETE.
    
    $sql = "DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':task_id' => $task_id,
        ':user_id' => $user_id
    ]);

    // Chuyển hướng về trang chủ với thông báo thành công
    redirect('index.php?status=deleted');

} catch (PDOException $e) {
    // Xử lý lỗi (ví dụ: hiển thị trang lỗi hoặc chuyển hướng với thông báo lỗi)
    // Đơn giản là chuyển hướng về index
    redirect('index.php?status=delete_failed');
}
?>