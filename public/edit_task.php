<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? null;
$errors = [];
$task = null;

if (!$task_id) {
    redirect('index.php');
}

// ------ XỬ LÝ NHANH 'MARK AS COMPLETE' TỪ index.php ------
if (isset($_GET['complete']) && $_GET['complete'] == 'true') {
    try {
        // Bảo mật: Đảm bảo người dùng chỉ cập nhật task của CHÍNH HỌ
        $sql = "UPDATE tasks SET status = 'completed' WHERE id = :task_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':task_id' => $task_id, ':user_id' => $user_id]);
        redirect('index.php?status=updated');
    } catch (PDOException $e) {
        $errors[] = "Lỗi CSDL: " . $e->getMessage();
    }
}

// ------ XỬ LÝ POST (Khi người dùng gửi form chỉnh sửa) ------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $status = $_POST['status'];

    // Validate
    if (empty($title)) {
        $errors[] = 'Tiêu đề là bắt buộc.';
    }
    if (!in_array($status, ['pending', 'in_progress', 'completed'])) {
        $errors[] = 'Trạng thái không hợp lệ.';
    }

    if (empty($errors)) {
        try {
            // Bảo mật: Luôn thêm `AND user_id = :user_id` vào mệnh đề WHERE
            $sql = "UPDATE tasks SET 
                        title = :title, 
                        description = :description, 
                        due_date = :due_date, 
                        status = :status 
                    WHERE id = :task_id AND user_id = :user_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':due_date' => $due_date,
                ':status' => $status,
                ':task_id' => $task_id,
                ':user_id' => $user_id
            ]);

            redirect('index.php?status=updated');

        } catch (PDOException $e) {
            $errors[] = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

// ------ XỬ LÝ GET (Lấy thông tin task để hiển thị lên form) ------
if (!$task) { // Chỉ lấy nếu chưa xử lý POST (để giữ lại dữ liệu POST nếu có lỗi)
    try {
        // Bảo mật: Đảm bảo người dùng chỉ lấy task của CHÍNH HỌ
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $stmt->execute([':task_id' => $task_id, ':user_id' => $user_id]);
        $task = $stmt->fetch();

        // Nếu không tìm thấy task (hoặc task không thuộc sở hữu của user)
        if (!$task) {
            // Có thể hiển thị trang 404 hoặc 403, ở đây ta chuyển hướng về index
            redirect('index.php');
        }
    } catch (PDOException $e) {
        $errors[] = "Lỗi CSDL: " . $e->getMessage();
        $task = []; // Gán rỗng để form không bị lỗi
    }
}


include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Chỉnh sửa công việc</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($task): // Chỉ hiển thị form nếu tìm thấy task ?>
                <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề công việc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($task['title'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả (Tùy chọn)</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Ngày hết hạn (Tùy chọn)</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="<?php echo htmlspecialchars($task['due_date'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" <?php echo ($task['status'] == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                <option value="in_progress" <?php echo ($task['status'] == 'in_progress') ? 'selected' : ''; ?>>Đang thực hiện</option>
                                <option value="completed" <?php echo ($task['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>