<?php
require_once '../config/db.php';
require_once '../includes/auth_check.php'; // Kiểm tra đăng nhập

$user_id = $_SESSION['user_id'];

// Xử lý Lọc và Sắp xếp 
$sort = $_GET['sort'] ?? 'due_date';
$filter_status = $_GET['filter_status'] ?? 'all'; 

// Xây dựng câu truy vấn SQL cơ bản 
$sql = "SELECT * FROM tasks WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

if ($filter_status !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $filter_status;
}

$safe_sort_columns = ['due_date', 'created_at', 'status', 'title'];
$sort_order = 'ASC';
if (in_array($sort, $safe_sort_columns) || str_ends_with($sort, '_desc')) {
    if (str_ends_with($sort, '_desc')) {
        $sort = substr($sort, 0, -5);
        $sort_order = 'DESC';
    }
    if (in_array($sort, $safe_sort_columns)) {
         $sql .= " ORDER BY $sort $sort_order";
    }
} else {
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


function getStatusClass($status) {
    switch ($status) {
        case 'pending': return ['border' => 'border-warning', 'text' => 'text-warning', 'badge' => 'bg-warning text-dark'];
        case 'in_progress': return ['border' => 'border-info', 'text' => 'text-info', 'badge' => 'bg-info text-dark'];
        case 'completed': return ['border' => 'border-success', 'text' => 'text-success', 'badge' => 'bg-success'];
        default: return ['border' => 'border-secondary', 'text' => 'text-secondary', 'badge' => 'bg-secondary'];
    }
}

include '../includes/header.php';
?>

<h3>Danh sách công việc </h3>
<hr>

<form method="GET" action="index.php" class="row g-3 mb-4 align-items-end p-2 bg-light rounded shadow-sm">
    <div class="col-md-5">
        <label for="filter_status" class="form-label mb-0 small text-muted">Lọc theo trạng thái:</label>
        <select name="filter_status" id="filter_status" class="form-select form-select-sm">
            <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Tất cả</option>
            <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
            <option value="in_progress" <?php echo ($filter_status == 'in_progress') ? 'selected' : ''; ?>>Đang thực hiện</option>
            <option value="completed" <?php echo ($filter_status == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
        </select>
    </div>
    <div class="col-md-5">
        <label for="sort" class="form-label mb-0 small text-muted">Sắp xếp theo:</label>
        <select name="sort" id="sort" class="form-select form-select-sm">
            <option value="due_date" <?php echo ($sort == 'due_date') ? 'selected' : ''; ?>>Ngày hết hạn (Sớm nhất)</option>
            <option value="due_date_desc" <?php echo ($sort == 'due_date_desc') ? 'selected' : ''; ?>>Ngày hết hạn (Muộn nhất)</option>
            <option value="created_at_desc" <?php echo ($sort == 'created_at_desc') ? 'selected' : ''; ?>>Ngày tạo (Mới nhất)</option>
            <option value="title" <?php echo ($sort == 'title') ? 'selected' : ''; ?>>Tiêu đề (A-Z)</option>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-filter"></i> Lọc</button>
    </div>
</form>

<?php if (isset($_GET['status'])): ?>
    <?php
        $message = match($_GET['status']) {
            'added' => 'Công việc đã được thêm thành công.',
            'updated' => 'Công việc đã được cập nhật thành công.',
            'deleted' => 'Công việc đã được xóa thành công.',
            default => null,
        };
        if ($message):
    ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
<?php endif; ?>


<?php if (empty($tasks)): ?>
    <div class="alert alert-info text-center mt-5">
        <i class="fa fa-frown fa-2x mb-2"></i><br>
        Bạn chưa có công việc nào trong danh sách hiện tại.
        Hãy <a href="add_task.php">thêm công việc mới</a>.
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($tasks as $task): 
            $status_class = getStatusClass($task['status']);
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-start border-5 <?php echo $status_class['border']; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 me-3 text-truncate">
                                <?php echo htmlspecialchars($task['title']); ?>
                            </h5>
                            <small class="text-nowrap text-muted small">
                                <i class="fa fa-calendar-alt me-1"></i> 
                                <?php echo ($task['due_date']) ? date('d/m/Y', strtotime($task['due_date'])) : 'Không Hạn'; ?>
                            </small>
                        </div>
                        
                        <p class="card-text text-muted small mb-3">
                            <?php echo nl2br(htmlspecialchars(substr($task['description'] ?? 'Không có mô tả.', 0, 100))); ?>...
                        </p>
                        
                        <span class="badge <?php echo $status_class['badge']; ?> mb-3">
                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                        </span>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center bg-white">
                        <div>
                             <small class="text-muted">Tạo lúc: <?php echo date('H:i d/m/Y', strtotime($task['created_at'])); ?></small>
                        </div>
                        <div class="btn-group" role="group">
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
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>