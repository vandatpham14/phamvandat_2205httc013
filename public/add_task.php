<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$title = '';
$description = '';
$due_date = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    // Validate
    if (empty($title)) {
        $errors[] = 'Tiêu đề là bắt buộc.';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO tasks (user_id, title, description, due_date, status) VALUES (:user_id, :title, :description, :due_date, 'pending')";
            $stmt = $pdo->prepare($sql);
            
            // Sử dụng Prepared Statements
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':due_date', $due_date); // PDO tự xử lý NULL

            $stmt->execute();

            // Chuyển hướng về trang chủ với thông báo thành công
            redirect('index.php?status=added');

        } catch (PDOException $e) {
            $errors[] = "Lỗi CSDL: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Thêm công việc mới</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="add_task.php" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề công việc <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả (Tùy chọn)</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Ngày hết hạn (Tùy chọn)</label>
                        <input type="date" class="form-control" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary">Thêm công việc</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>