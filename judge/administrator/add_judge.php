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

// 處理新增資料的表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_judge'])) {
    // 獲取表單資料
    $jname = $_POST['jname'];
    $jtitle = $_POST['jtitle'];
    $jphone = $_POST['jphone'];
    $jemail = $_POST['jemail'];

    // 檢查表單資料是否有空值
if ( !empty($jname) && !empty($jtitle) && !empty($jphone) && !empty($jemail)) {
    // 插入資料到資料庫
    $sql = "INSERT INTO judge_data ( jname, jtitle, jphone, jemail) VALUES ( ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $jname, $jtitle, $jphone, $jemail);  // 注意這裡的bind_param
    
    if ($stmt->execute()) {
        $message = "資料新增成功！";
    } else {
        $message = "資料新增失敗，請稍後再試。";
    }

    $stmt->close();
} else {
    $message = "所有欄位都必須填寫！";
}

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
        .form-container {
            margin: 20px 0;
        }
        .form-container input {
            padding: 8px;
            margin: 10px;
            width: 100%;
            max-width: 300px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #b58c51;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }
        .form-container button:hover {
            background-color: #946f3a;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>所有評審委員資料</h2>

        <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

        <!-- 新增評審表單 -->
        <div class="form-container">
            <h3>新增評審委員</h3>
            <form action="" method="post">
  		
                <input type="text" name="jname" placeholder="姓名" required>
                <input type="text" name="jtitle" placeholder="職稱" required>
                <input type="text" name="jphone" placeholder="電話" required>
                <input type="email" name="jemail" placeholder="電子郵件" required>
                <button type="submit" name="add_judge">新增評審</button>
            </form>
        </div>

        <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>

        
        <!-- 回到主頁按鈕 -->
        <a href="main.php">
            <button class="back-btn">回到主頁</button>
        </a>
    </div>

</body>
</html>

