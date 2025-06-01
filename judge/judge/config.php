<?php
$servername = "localhost";
$username = "root";  // 資料庫使用者名稱
$password = "12345678";      // 資料庫密碼
$dbname = "judge";  // 你的資料庫名稱

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
?>
