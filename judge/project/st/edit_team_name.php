<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

$team_id = $_GET['team_id'] ?? null;
if ($team_id) {
    // 查詢隊伍資料
    $query = "SELECT * FROM team WHERE Team_ID = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $team = $result->fetch_assoc();
        // 此處的stmt是第一次查詢，後續會關閉
    } else {
        echo "資料庫錯誤: " . $conn->error;
    }
}

// 處理隊伍名稱更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = $_POST['team_name'];

    // 更新隊伍名稱
    $update_query = "UPDATE team SET Team_Name = ? WHERE Team_ID = ?";
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("ss", $team_name, $team_id);
        if ($stmt->execute()) {
            // 更新成功後重新抓取最新資料
            $query = "SELECT * FROM team WHERE Team_ID = ?";
            if ($stmt_select = $conn->prepare($query)) {  // 使用新的stmt變數
                $stmt_select->bind_param("s", $team_id);
                $stmt_select->execute();
                $result = $stmt_select->get_result();
                $team = $result->fetch_assoc(); // 更新資料
                $stmt_select->close();  // 這裡關閉新的stmt
            } else {
                echo "資料庫錯誤: " . $conn->error;
            }
            echo "<p style='color: green;'>隊伍名稱已更新。</p>";
        } else {
            echo "<p style='color: red;'>更新失敗，請再試一次。</p>";
        }
        $stmt->close();  // 關閉更新語句的stmt
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改隊伍名稱</title>
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
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>修改隊伍名稱</h2>

        <!-- 更新提示 -->
        <div class="message">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p style="color: green;">隊伍名稱已成功更新！</p>
            <?php endif; ?>
        </div>

        <!-- 表單 -->
        <form action="edit_team_name.php?team_id=<?php echo $team_id; ?>" method="POST">
            <label for="team_name">隊伍名稱:</label>
            <input type="text" name="team_name" value="<?php echo htmlspecialchars($team['Team_Name']); ?>" required>
            
            <button type="submit">更新名稱</button>
        </form>

        <!-- 返回按鈕 -->
        <a href="main.php" class="back-button">返回隊伍頁面</a>
    </div>
</body>
</html>


