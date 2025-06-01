<?php

// 啟動 session
session_start();

// 初始化錯誤訊息變數
$error_message = "";

// 檢查是否有表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // 取得表單資料
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 查詢 judge_data 資料表，根據帳號 (jid)
    $sql = "SELECT * FROM judge_data WHERE jid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 取得資料
        $row = $result->fetch_assoc();
        $stored_jname = $row['jname'];

        // 驗證密碼是否與 jname 相同
        if ($stored_jname == $pass) {
            // 登入成功，儲存 jid 到 session
            $_SESSION['jid'] = $row['jid'];

            // 確保沒有其他輸出後再進行 header 跳轉
            ob_clean();  // 清除任何緩衝區中的輸出
	    echo "準備跳轉到 main1.php";
            header("Location: main1.php");
            exit(); // 確保跳轉後不再執行後續程式
        } else {
            // 密碼錯誤
            $error_message = "帳號或密碼錯誤，請重新輸入！";
        }
    } else {
        // 帳號不存在
        $error_message = "帳號不存在，請重新輸入！";
    }

    // 關閉連線
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>評審登入</title>
    <style>
        /* CSS 樣式 */
        body {
            margin: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #ffffff;
            background-image: linear-gradient(315deg, #ffffff 0%, #d7e1ec 74%);
        }
        .container {
            width: 300px;
            height: auto;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container_header {
            margin: 10px auto;
            color: #595859;
            font-family: Microsoft JhengHei;
        }
        .form {
            display: flex;
            width: 100%;
            flex-direction: column;
            align-items: center;
        }
        .form--input {
            width: 200px;
            background-color: transparent;
            border: 0;
            border-bottom: 1px dashed #c2c3c4;
            margin-bottom: 20px;
            color: black;
            height: 30px;
        }
        .form--btn {
            background-color: transparent;
            border: 0;
            color: #595859;
            height: 40px;
            width: 100%;
        }
        .form--btn:hover {
            opacity: 0.8;
            background-color: rgba(189, 184, 212, 0.8);
        }
        .form--link {
            margin-top: 10px;
            font-size: 14px;
            color: #595859;
            text-decoration: none;
        }
        .form--link:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="container_header">
            <h1>評審登入</h1>
        </div>
        <form action="" method="POST" class="form">
            <!-- 如果有錯誤訊息，顯示在此處 -->
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <input type="text" class="form--input" name="username" placeholder="帳號" required>
            <input type="password" class="form--input" name="password" placeholder="密碼" required>
            <button type="submit" a href="main1.html" class="form--btn">登入</button>
            <a href="forget_password.html" class="form--link">忘記密碼？</a>
        </form>
    </div>
</body>
</html>


