<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chào Mừng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <?php
            // Kiểm tra xem dữ liệu 'username' có tồn tại trong mảng POST không
            if (isset($_POST['username'])) {
                // Lấy tên người dùng từ mảng $_POST
                $username = htmlspecialchars($_POST['username']); 
                // Hiển thị lời chào với định dạng h2
                echo "<h2>Chào mừng, $username!</h2>";
            } else {
                // Hiển thị thông báo nếu truy cập trực tiếp
                echo "<h2>Không tìm thấy thông tin đăng nhập.</h2>";
            }
            ?>
        </div>
    </div>
</div>

</body>
</html>