<?php
// 啟動 Session
session_start();

// 資料庫連接設置
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "judge"; // 資料庫名稱

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連接失敗: " . $conn->connect_error);
}

// 確保評審的 jid 存在
$judge_id = $_SESSION['jid'] ?? null;

// 如果沒有 jid，則重定向回登入頁面
if (!$judge_id) {
    header("Location: login.php");
    exit();
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['scores']) && isset($_POST['comments'])) {
        $message = '';
        foreach ($_POST['scores'] as $team_id => $score) {
            $comment = $_POST['comments'][$team_id];

            // 檢查 score 是否為有效數字
            if (!is_numeric($score) || $score < 0 || $score > 100) {
                $message = "分數必須在 0 到 100 之間。";
                break;
            }

            // 檢查 comment 是否為非空字符串
            if (empty($comment)) {
                $message = "評論不能為空。";
                break;
            }

            // 插入到 scores 表，使用 jid
            $stmt = $conn->prepare("INSERT INTO scores (jid, TEAM_ID, score, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $judge_id, $team_id, $score, $comment);

            if (!$stmt->execute()) {
                $message = "評分失敗：" . $stmt->error;
                break;
            }

            $stmt->close();
        }

        // 顯示訊息
        if (!$message) {
            $message = "評分成功！";
            // 提交評分後跳轉到 main1.php
            header("Location: main1.php");
            exit();
        }
    }
}

// 查詢隊伍及作品資訊
$sql = "
    SELECT 
        t.team_id, 
        w.work_name, 
        w.work_description, 
        w.work_poster, 
        w.work_video, 
        w.work_code
    FROM 
        team t
    LEFT JOIN 
        works w ON t.team_id = w.team_id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隊伍評分系統</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        h1 {
            text-align: center;
            margin: 20px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            background-color: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        textarea {
            width: 90%;
            height: 50px;
            resize: none;
        }
        input[type="number"] {
            width: 80px;
        }
        button {
            padding: 10px 20px;
            margin: 20px auto;
            display: block;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>隊伍評分系統</h1>

    <?php if (isset($message)) { echo "<p style='text-align: center; color: green;'>$message</p>"; } ?>

    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>隊伍編號</th>
                    <th>作品名稱</th>
                    <th>作品簡介</th>
                    <th>作品海報</th>
                    <th>影片連結</th>
                    <th>程式碼下載</th>
                    <th>分數</th>
                    <th>評論</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0) { 
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['team_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['work_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['work_description']); ?></td>
                            <td>
                                <?php if ($row['work_poster']) { ?>
                                    <img src="<?php echo htmlspecialchars($row['work_poster']); ?>" alt="海報">
                                <?php } else { ?>
                                    無海報
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['work_video']) { ?>
                                    <a href="<?php echo htmlspecialchars($row['work_video']); ?>" target="_blank">觀看影片</a>
                                <?php } else { ?>
                                    無影片
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['work_code']) { ?>
                                    <a href="<?php echo htmlspecialchars($row['work_code']); ?>" target="_blank">下載程式碼</a>
                                <?php } else { ?>
                                    無程式碼
                                <?php } ?>
                            </td>
                            <td>
                                <input type="number" name="scores[<?php echo $row['team_id']; ?>]" min="0" max="100" required>
                            </td>
                            <td>
                                <textarea name="comments[<?php echo $row['team_id']; ?>]" placeholder="輸入評論"></textarea>
                            </td>
                        </tr>
                <?php } } else { ?>
                    <tr>
                        <td colspan="8">目前沒有隊伍資料。</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <button type="submit">提交評分</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>

