<?php

require_once "db.php";

$uname = isset($_POST['uname']) ? $_POST['uname'] : "";
$email = isset($_POST['email']) ? $_POST['email'] : "";

// Câu lệnh SQL để chèn dữ liệu
$sql = "insert into users (name, email, date_created) values (?, ?, ?)";

// Tạo một thể hiện (instance) của lớp DbHelper
$db = new DbHelper();

try {
    // Thực thi câu lệnh SQL INSERT
    $last_id = $db->insert($sql, [$uname, $email, date('Y-m-d H:i:s')]);

    // Hiển thị ID mới (Chỉ chạy khi thành công)
    echo "✅ Dữ liệu đã được chèn thành công! ID mới được chèn: **" . $last_id . "**";

} catch (Exception $e) {
    // XỬ LÝ VÀ HIỂN THỊ LỖI
    echo "❌ LỖI KHÔNG CHÈN ĐƯỢC DỮ LIỆU HOẶC KẾT NỐI CSDL:";
    echo "<br>Chi tiết lỗi: **" . $e->getMessage() . "**";
    $last_id = 0; // Đặt ID là 0 nếu lỗi
}

?>