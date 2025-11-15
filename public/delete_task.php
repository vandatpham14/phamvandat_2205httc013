<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Đảm bảo đã đăng nhập

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    redirect('index.php');
}

try {
    
    $sql = "DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':task_id' => $task_id,
        ':user_id' => $user_id
    ]);

    redirect('index.php?status=deleted');

} catch (PDOException $e) {
    redirect('index.php?status=delete_failed');
}
?>