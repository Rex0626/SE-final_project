<?php
// 啟動 session
session_start();

// 檢查是否登入成功，並取得 jname
if (!isset($_SESSION['jname'])) {
    // 如果沒有登入，重新導向到登入頁面
    header("Location: judge.php");
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

// 查詢資料庫，取得所有評審資料
$sql = "SELECT * FROM judge_data";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 資料查詢成功，逐條取得資料
    $judges = [];
    while ($row = $result->fetch_assoc()) {
        $judges[] = $row;
    }
} else {
    $error_message = "找不到任何評審資料";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>評審委員資料</title>
    <style>
        body {
            font-family: Microsoft JhengHei;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #635031;
        }
        .info {
            margin: 15px 0;
            font-size: 18px;
        }
        .back-btn {
            background-color: #b58c51;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        .back-btn:hover {
            background-color: #946f3a;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>所有評審委員資料</h2>
        <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>
        
        <?php foreach ($judges as $judge) { ?>
            <div class="info">
                <strong>評審編號：</strong> <?php echo htmlspecialchars($judge['jid']); ?>
            </div>
            <div class="info">
                <strong>姓名：</strong> <?php echo htmlspecialchars($judge['jname']); ?>
            </div>
            <div class="info">
                <strong>職稱：</strong> <?php echo htmlspecialchars($judge['jtitle']); ?>
            </div>
            <div class="info">
                <strong>電話：</strong> <?php echo htmlspecialchars($judge['jphone']); ?>
            </div>
            <div class="info">
                <strong>電子郵件：</strong> <?php echo htmlspecialchars($judge['jemail']); ?>
            </div>
            <hr> <!-- 隔開不同評審的資料 -->
        <?php } ?>

        <!-- 回到主頁按鈕 -->
        <a href="main.php">
            <button class="back-btn">回到主頁</button>
        </a>
    </div>

</body>
</html>
