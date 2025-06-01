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

// 查詢資料庫，獲取發布日期、公告內容和海報
$sql = "
SELECT 
    postdate, 
    word, 
    paper
FROM 
    post
ORDER BY 
    postdate DESC;  -- 按照發布日期降序排列
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 資料查詢成功，逐條取得資料
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'postdate' => $row['postdate'],
            'word' => $row['word'],
            'paper' => $row['paper']
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
    <title>公告資料</title>
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
        img {
            max-width: 100%; /* 讓圖片適應容器大小 */
            height: auto;   /* 保持圖片比例 */
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>公告資料</h2>
        <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>
        
        <?php foreach ($posts as $post_data) { ?>
            <div class="info">
                <strong>發布日期：</strong> <?php echo htmlspecialchars($post_data['postdate']); ?><br>
                <strong>公告內容：</strong> <?php echo nl2br(htmlspecialchars($post_data['word'])); ?><br>
                <strong>海報：</strong><br>
                <?php if (!empty($post_data['paper'])) { ?>
                    <img src="<?php echo htmlspecialchars($post_data['paper']); ?>" alt="海報圖片">
                <?php } else { ?>
                    <p>沒有海報。</p>
                <?php } ?>
            </div>
            <hr> <!-- 隔開不同公告的資料 -->
        <?php } ?>

        <!-- 回到主頁按鈕 -->
        <a href="main.php">
            <button class="back-btn">回到主頁</button>
        </a>
    </div>

</body>
</html>

