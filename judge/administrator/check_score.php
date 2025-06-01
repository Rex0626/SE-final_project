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

// 查詢資料庫，獲取隊伍編號、評審編號、分數、評論
$sql = "
SELECT 
    team_id, 
    jid, 
    score, 
    comment
FROM 
    scores
ORDER BY 
    team_id;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 資料查詢成功，逐條取得資料
    $scores = [];
    while ($row = $result->fetch_assoc()) {
        $scores[] = [
            'team_id' => $row['team_id'],
            'jid' => $row['jid'],
            'score' => $row['score'],
            'comment' => $row['comment']
        ];
    }
} else {
    $error_message = "找不到任何資料";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>隊伍評分資料</title>
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
        hr {
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>隊伍評分資料</h2>
        <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>
        
        <?php foreach ($scores as $score_data) { ?>
            <div class="info">
                <strong>隊伍編號：</strong> <?php echo htmlspecialchars($score_data['team_id']); ?><br>
                <strong>評審編號：</strong> <?php echo htmlspecialchars($score_data['jid']); ?><br>
                <strong>分數：</strong> <?php echo htmlspecialchars($score_data['score']); ?><br>
                <strong>評論：</strong> <?php echo nl2br(htmlspecialchars($score_data['comment'])); ?><br>
            </div>
            <hr> <!-- 隔開不同隊伍的評分資料 -->
        <?php } ?>

        <!-- 回到主頁按鈕 -->
        <a href="main.php">
            <button class="back-btn">回到主頁</button>
        </a>
    </div>

</body>
</html>

