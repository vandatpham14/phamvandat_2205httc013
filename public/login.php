<?php
require_once '../config/db.php'; // Đã bao gồm session_start()

$error = '';
$username = '';

// Nếu đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập Tên đăng nhập và Mật khẩu.';
    } else {
        try {
            // 1. Tìm người dùng bằng username
            // Sử dụng Prepared Statement
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 2. Xác thực mật khẩu
            if ($user && password_verify($password, $user['password'])) {
                // 3. Đăng nhập thành công: Lưu thông tin vào Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // 4. Chuyển hướng đến trang chủ (Dashboard)
                redirect('index.php');
            } else {
                // Đăng nhập thất bại
                $error = 'Tên đăng nhập hoặc Mật khẩu không chính xác.';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi CSDL: ' . $e->getMessage();
        }
    }
}

// Hiển thị giao diện
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Đăng nhập</h3>
            </div>
            <div class="card-body">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
                    <div class="alert alert-success">
                        Đăng ký thành công! Vui lòng đăng nhập.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['session_expired'])): ?>
                    <div class="alert alert-warning">
                        Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Chưa có tài khoản? <a href="register.php">Đăng ký</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>