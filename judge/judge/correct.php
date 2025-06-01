<?php
// 啟動 session
session_start();

// 確認是否登入
if (!isset($_SESSION['jid'])) {
    header("Location: judge.php");
    exit;
}

$jid = $_SESSION['jid']; // 從 session 取得 jid

// MySQL 資料庫連線設定
$servername = "localhost";
$username = "root"; // 替換為 MySQL 使用者名稱
$password = "12345678"; // 替換為 MySQL 密碼
$dbname = "judge"; // 替換為資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 查詢使用者資料
$sql = "SELECT * FROM judge_data WHERE jid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $jid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['jname'];
    $title = $row['jtitle'];
    $phone = $row['jphone'];
    $email = $row['jemail'];
} else {
    echo "資料未找到";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>修改個人資料</title>
    <style>
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            background-color: #fffffc;
            background-image: linear-gradient(315deg, #fffffc 0%, #dbd2c1 74%);
            background-attachment: fixed;
            background-size: cover;
            font-family: Microsoft JhengHei;
        }
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #f7f5f2;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .fixed-header h1 {
            margin: 0;
            padding: 20px 0;
            font-size: 36px;
            color: #635031;
            text-align: center;
        }
        .main-content {
            margin-top: 150px;
            padding: 20px;
            text-align: center;
        }
        .form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 auto;
            width: 80%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form--input {
            width: 100%;
            border: 1px solid #c2c3c4;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
        }
        .form--btn {
            background-color: #b58c51;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .form--btn:hover {
            background-color: #946f3a;
        }
    </style>
</head>
<body>
    <!-- 頁首 -->
    <div class="fixed-header">
        <h1>修改個人資料</h1>
    </div>
    <!-- 主內容 -->
    <div class="main-content">
        <h2>個人資料</h2>
        <p>請修改您的個人資料。</p>
        <form action="submit_profile.php" method="POST" class="form">
            <div>
                <label for="name">姓名：</label> 
                <input type="text" class="form--input" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div>
                <label for="title">職稱：</label> 
                <input type="text" class="form--input" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div>
                <label for="phone">電話：</label> 
                <input type="text" class="form--input" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div>
                <label for="email">電子郵件：</label> 
                <input type="email" class="form--input" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <button type="submit" class="form--btn">確認修改</button>
        </form>
    </div>
</body>
</html>

