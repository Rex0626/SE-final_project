<?php
session_start();
require_once 'config.php'; // 資料庫連接設定

// 確保使用者已經登入
if (!isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

// 確保有傳遞 work_id
if (!isset($_GET['work_id'])) {
    echo "錯誤: 缺少作品 ID！";
    exit();
}

$work_id = $_GET['work_id'];
$team_id = $_SESSION['team_id'];

// 查詢該作品的資料
$query = "SELECT * FROM works WHERE Work_ID = ? AND Team_ID = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("ss", $work_id, $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $work = $result->fetch_assoc();
    $stmt->close();
    
    if (!$work) {
        echo "錯誤: 未找到此作品！";
        exit();
    }
} else {
    echo "資料庫錯誤: " . $conn->error;
    exit();
}

// 處理表單提交以更新作品描述
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_description'])) {
    $new_description = trim($_POST['new_description']);
    
    if (empty($new_description)) {
        echo "請輸入新的描述！";
    } else {
        $update_query = "UPDATE works SET Work_Description = ? WHERE Work_ID = ? AND Team_ID = ?";
        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("sss", $new_description, $work_id, $team_id);
            if ($stmt->execute()) {
                echo "<p class='message success-message'>作品描述已成功更新！</p>";
            } else {
                echo "<p class='message error-message'>更新失敗，請再次操作！</p>";
            }
            $stmt->close();
        }
    }
}

// 關閉資料庫連接
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改作品描述</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        label {
            font-size: 16px;
            margin-bottom: 10px;
            display: block;
            color: #555;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
        }
        button {
            background-color: #5c9f5f;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #4a7c4a;
        }
        .back-button {
            background-color: #ccc;
            margin-top: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            display: block;
            width: 100%;
            text-align: center;
        }
        .back-button:hover {
            background-color: #bbb;
        }
        .message {
            margin: 20px 0;
            font-size: 16px;
            text-align: center;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>修改作品描述</h2>

        <form action="" method="post">
            <label for="new_description">作品描述:</label>
            <textarea name="new_description" id="new_description" rows="5" required><?php echo htmlspecialchars($work['Work_Description']); ?></textarea>
            <button type="submit">更新描述</button>
        </form>

        <a href="main.php" class="back-button">返回隊伍頁面</a>
    </div>
</body>
</html>



