<?php
// 啟動 session
session_start();

// 檢查是否登入成功，並取得 jname
if (!isset($_SESSION['jname'])) {
    // 如果沒有登入，重新導向到登入頁面
    header("Location: administrator.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得公告內容
    $word = isset($_POST['word']) ? $_POST['word'] : '';
    
    // 取得海報 URL
    $paper = isset($_POST['paper']) ? $_POST['paper'] : '';

    // 檢查海報 URL 是否有效
    if (filter_var($paper, FILTER_VALIDATE_URL)) {
        // 取得當前時間作為公告發布日期
        $postdate = date("Y-m-d H:i:s");

        // 將資料存入資料庫
        $sql = "INSERT INTO post (postdate, word, paper) VALUES ('$postdate', '$word', '$paper')";

        if ($conn->query($sql) === TRUE) {
            $success_message = "公告已成功發布！";
        } else {
            $error_message = "錯誤：無法發布公告。";
        }
    } else {
        $error_message = "錯誤：請提供有效的海報 URL。";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>發布公告</title>
    <style>
        body {
            font-family: Microsoft JhengHei;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>發布公告</h2>
    <form method="POST">
        <!-- 公告內容 -->
        <div class="form-group">
            <label for="word">公告內容</label>
            <textarea id="word" name="word" required></textarea>
        </div>

        <!-- 海報 URL -->
        <div class="form-group">
            <label for="paper">海報 URL</label>
            <input type="text" id="paper" name="paper" placeholder="請輸入海報的 URL" required>
        </div>

        <!-- 提交按鈕 -->
        <div class="form-group">
            <button type="submit" class="btn-submit">發布公告</button>
        </div>
    </form>
</body>
</html>


