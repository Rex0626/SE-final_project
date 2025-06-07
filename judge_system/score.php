<?php
session_start();

// 檢查登入與角色
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge' ) {
    header("Location: login.php");
    exit();
}

$judgeId = $_SESSION['judge_id'];

// Supabase 資料庫 API 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// 提交表單處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = $_POST['scores'] ?? [];
    $comments = $_POST['comments'] ?? [];
    $success = true;

    foreach ($scores as $workId => $score) {
        $comment = $comments[$workId] ?? '';

        if (!is_numeric($score) || $score < 0 || $score > 100 || trim($comment) === '') {
            $success = false;
            break;
        }

        $payload = json_encode([
            'JudgeID' => $judgeId,
            'WorkID' => $workId,
            'Score' => (int)$score,
            'Comments' => $comment
        ]);

        $ch = curl_init("$apiUrl/Evaluations");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $apiKey",
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo "<script>alert('評分成功！'); window.location.href='judge_system.php';</script>";
        exit();
    } else {
        echo "<script>alert(' 評分失敗，請檢查資料格式或稍後再試。');</script>";
    }
}

//讀取作品資訊
$ch = curl_init("$apiUrl/Works");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$works = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>作品評分上傳</title>
    <style>
        body {
            font-family: Microsoft JhengHei;
            background-color: #f5f5f5;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }
        textarea {
            width: 90%;
            height: 60px;
        }
        input[type="number"] {
            width: 70px;
        }
        button {
            margin-top: 20px;
            padding: 10px 30px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
	.back-btn {
  	    position: absolute;
  	    top: 20px;
  	    right: 130px; 
  	    background-color: #6c757d;
  	    color: white;
  	    padding: 10px 18px;
  	    border: none;
  	    border-radius: 5px;
  	    font-size: 14px;
  	    text-decoration: none;
  	    transition: background-color 0.3s;
	}
	.back-btn:hover {
  	    background-color: #5a6268;
	}
    </style>
</head>
<body>
    <h2>評審評分系統</h2>
    <a href="judge_system.php" class="back-btn">返回主頁</a>
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th>作品編號</th>
                    <th>簡介</th>
                    <th>海報</th>
                    <th>影片連結</th>
                    <th>程式碼</th>
                    <th>分數</th>
                    <th>評論</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td><?= htmlspecialchars($work['WorkID']) ?></td>
                       
                        <td><?= htmlspecialchars($work['Description'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($work['Poster'])): ?>
                                <img src="<?= htmlspecialchars($work['Poster']) ?>" width="100">
                            <?php else: ?>
                                無
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($work['VideoLink'])): ?>
                                <a href="<?= htmlspecialchars($work['VideoLink']) ?>" target="_blank">觀看</a>
                            <?php else: ?>
                                無
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($work['CodeLink'])): ?>
                                <a href="<?= htmlspecialchars($work['CodeLink']) ?>" target="_blank">下載</a>
                            <?php else: ?>
                                無
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" name="scores[<?= $work['WorkID'] ?>]" min="0" max="100" required>
                        </td>
                        <td>
                            <textarea name="comments[<?= $work['WorkID'] ?>]" required></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="text-align:center">
            <button type="submit">提交評分</button>
        </div>
	
    </form>
</body>
</html>




