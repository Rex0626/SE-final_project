<?php
// 啟動 session
session_start();

// 檢查是否登入
if (!isset($_SESSION['jid'])) {
    header("Location: judge.php");
    exit;
}

$jid = $_SESSION['jid']; // 從 session 取得 jid

// 檢查是否有 POST 資料
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得表單資料
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // 驗證資料
    if (empty($name) || empty($title) || empty($phone) || empty($email)) {
        echo "所有欄位均為必填！";
        exit;
    }

    // MySQL 資料庫連線設定
    $servername = "localhost";
    $username = "root"; // 替換為 MySQL 使用者名稱
    $password = "12345678"; // 替換為 MySQL 密碼
    $dbname = "judge"; // 替換為實際資料庫名稱

    // 建立連線
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 檢查連線是否成功
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    // 更新資料庫
    $sql = "UPDATE judge_data SET jname = ?, jtitle = ?, jphone = ?, jemail = ? WHERE jid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("資料庫查詢準備失敗: " . $conn->error);
    }

    $stmt->bind_param("sssss", $name, $title, $phone, $email, $jid);
    if ($stmt->execute()) {
        // 更新成功，重新導向到 main1.php
        $stmt->close();
        $conn->close();
        header("Location: main1.php");
        exit;
    } else {
        echo "更新失敗：" . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "非法的請求方法";
    exit;
}

