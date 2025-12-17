<?php

class DbHelper {
    private $conn;

    public function __construct() {
        // Cấu hình CSDL
        $user = "root";
        $pass = ""; // Cập nhật mật khẩu CSDL của bạn nếu có
        $dbname = "user_management"; // Cập nhật tên CSDL của bạn
        
        // Chuỗi kết nối DSN: Dùng 'port=8088' để kết nối đúng cổng bạn đã cấu hình
        $dsn = "mysql:host=localhost;port=8088;dbname={$dbname}"; 

        try {
            // Thực hiện kết nối PDO
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ); 
            
        } catch (PDOException $e) {
            // Dừng chương trình và báo lỗi nếu không kết nối được
            die("❌ LỖI KẾT NỐI CSDL: " . $e->getMessage());
        }
    }

    public function insert($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $this->conn->lastInsertId();
    }

    public function select($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>