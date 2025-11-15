<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Kiểm tra đăng nhập

$user_id = $_SESSION['user_id'];

// Xử lý Lọc và Sắp xếp
$sort = $_GET['sort'] ?? 'due_date'; // Mặc định sắp xếp theo ngày hết hạn
$filter_status = $_GET['filter_status'] ?? 'all'; // Mặc định hiển thị tất cả

// Xây dựng câu truy vấn SQL cơ bản
$sql = "SELECT * FROM tasks WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

// Thêm điều kiện Lọc (Filter)
if ($filter_status !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $filter_status;
}

// Thêm điều kiện Sắp xếp (Sort)
// Chỉ cho phép sắp xếp theo các cột an toàn
$safe_sort_columns = ['due_date', 'created_at', 'status', 'title'];
if (in_array($sort, $safe_sort_columns)) {
    // Thêm ASC/DESC (ví dụ: 'due_date' hoặc 'due_date_desc')
    $sort_order = 'ASC';
    if (str_ends_with($sort, '_desc')) {
        $sort = substr($sort, 0, -5);
        $sort_order = 'DESC';
    }
    // Đảm bảo cột vẫn an toàn sau khi cắt
    if (in_array($sort, $safe_sort_columns)) {
         $sql .= " ORDER BY $sort $sort_order";
    }
} else {
    // Mặc định
    $sql .= " ORDER BY due_date ASC";
}


try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    $tasks = [];
}

// Hàm trợ giúp để lấy class màu cho status
function getStatusBadge($status) {
    switch ($status) {
        case 'pending': return 'bg-warning text-dark';
        case 'in_progress': return 'bg-info text-dark';
        case 'completed': return 'bg-success';
        default: return 'bg-secondary';
    }
}

include '../includes/header.php';
?>

<h3>Bảng điều khiển công việc</h3>
<hr>

<form method="GET" action="index.php" class="row g-3 mb-4 align-items-center">
    <div class="col-md-5">
        <label for="filter_status" class="form-label">Lọc theo trạng thái:</label>
        <select name="filter_status" id="filter_status" class="form-select">
            <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Tất cả</option>
            <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
            <option value="in_progress" <?php echo ($filter_status == 'in_progress') ? 'selected' : ''; ?>>Đang thực hiện</option>
            <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
        </select>
    </div>
    <div class="col-md-5">
        <label for="sort" class="form-label">Sắp xếp theo:</label>
        <select name="sort" id="sort" class="form-select">
            <option value="due_date" <?php echo ($sort == 'due_date') ? 'selected' : ''; ?>>Ngày hết hạn (Tăng dần)</option>
            <option value="due_date_desc" <?php echo ($sort == 'due_date_desc') ? 'selected' : ''; ?>>Ngày hết hạn (Giảm dần)</option>
            <option value="created_at_desc" <?php echo ($sort == 'created_at_desc') ? 'selected' : ''; ?>>Ngày tạo (Mới nhất)</option>
            <option value="title" <?php echo ($sort == 'title') ? 'selected' : ''; ?>>Tiêu đề (A-Z)</option>
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Áp dụng</button>
    </div>
</form>

<?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
    <div class="alert alert-success">Công việc đã được thêm thành công.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
    <div class="alert alert-success">Công việc đã được cập nhật thành công.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
    <div class="alert alert-success">Công việc đã được xóa thành công.</div>
<?php endif; ?>


<div class="list-group">
    <?php if (empty($tasks)): ?>
        <div class="alert alert-info">Bạn chưa có công việc nào. Hãy <a href="add_task.php">thêm công việc mới</a>.</div>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">
                        <?php echo htmlspecialchars($task['title']); ?>
                    </h5>
                    <small>
                        <?php if ($task['due_date']): ?>
                            Hết hạn: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                        <?php else: ?>
                            Không có ngày hết hạn
                        <?php endif; ?>
                    </small>
                </div>
                
                <p class="mb-1"><?php echo nl2br(htmlspecialchars($task['description'] ?? 'Không có mô tả.')); ?></p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge <?php echo getStatusBadge($task['status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                    </span>
                    <div>
                        <?php if ($task['status'] !== 'completed'): ?>
                            <a href="edit_task.php?id=<?php echo $task['id']; ?>&complete=true" class="btn btn-success btn-sm" title="Đánh dấu hoàn thành">
                                <i class="fa fa-check"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm" title="Chỉnh sửa">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa công việc này?');">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>