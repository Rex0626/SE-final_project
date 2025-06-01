<?php
session_start();
require_once 'config.php'; // 資料庫連接設定

$error_message = ""; // 儲存錯誤訊息

// 檢查是否有提交表單
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得表單提交的資料
    $team_id = $_POST['team_id'];
    $team_password = $_POST['team_password'];

    // 檢查隊伍ID和密碼是否正確
    $query = "SELECT * FROM team WHERE Team_ID = ? AND Team_Password = ?";
    if ($stmt = $conn->prepare($query)) {
        // 綁定參數
        $stmt->bind_param("ss", $team_id, $team_password);

        // 執行查詢
        $stmt->execute();
        $result = $stmt->get_result();

        // 檢查是否找到該隊伍
        if ($result->num_rows > 0) {
            // 登入成功，將隊伍資訊保存到 session
            $_SESSION['team_id'] = $team_id;
    	    $_SESSION['team_name'] = $result->fetch_assoc()['Team_Name']; // 儲存隊伍名稱
            header("Location: main.php"); // 轉到學生介面
            exit(); // 確保腳本結束，避免後續代碼執行
        } else {
            // 登入失敗，顯示錯誤訊息
            $error_message = "登入失敗，請確認隊伍ID與密碼";
        }

        $stmt->close();
    } else {
        echo "資料庫錯誤: " . $conn->error;
    }

    // 關閉資料庫連接
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>學生登入</title>
    <style type="text/css">
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
            height: 330px;
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
            height: 300px;
            width: 100%;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
        }
        .form--input {
            width: 200px;
            background-color: transparent;
            border: 0;
            border-bottom: 1px dashed #c2c3c4;
            margin-bottom: 30px;
            color: black;
            height: 30px;
        }
        .form--btn {
            background-color: transparent;
            border: 0;
            color: #595859;
            height: 50px;
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
            <h1 align="center">學生登入</h1>
        </div>
        <form action="login.php" method="POST" class="form">
            <?php if (isset($error_message)) { ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php } ?>
            <input type="text" name="team_id" class="form--input" placeholder="帳號" required>
            <input type="password" name="team_password" class="form--input" placeholder="密碼" required>
            <button type="submit" class="form--btn">登入</button>
            <a href="forget_password.html" class="form--link">忘記密碼？</a>
        </form>
    </div>
</body>
</html>


