<?php
session_start();

// 檢查登入與角色
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge' || !isset($_SESSION['judge_id'])) {
    header("Location: login.php");
    exit();
}

$judgeId = $_SESSION['judge_id'];

// Supabase 資料庫 API 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// 讀取所有作品
$ch = curl_init("$apiUrl/Works");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true
]);
$works = json_decode(curl_exec($ch), true);
curl_close($ch);

//讀取該評審的所有評分紀錄
$evalUrl = "$apiUrl/Evaluations?JudgeID=eq." . urlencode($judgeId);
$ch = curl_init($evalUrl);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true
]);
$evaluated = json_decode(curl_exec($ch), true);
curl_close($ch);

// 建立評分映射
$evaluatedMap = [];
foreach ($evaluated as $e) {
    $evaluatedMap[$e['WorkID']] = $e;
}

// 表單處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $scores = $_POST['scores'] ?? [];
    $comments = $_POST['comments'] ?? [];
    $success = true;

    foreach ($scores as $workId => $score) {
        $comment = trim($comments[$workId] ?? '');

        if (!is_numeric($score) || $score < 0 || $score > 100 || $comment === '') {
            $success = false;
            break;
        }

        $data = json_encode([
            'Score' => (int)$score,
            'Comments' => $comment
        ]);

        if (isset($evaluatedMap[$workId])) {
            // 已評分 → PATCH 更新
            $url = "$apiUrl/Evaluations?JudgeID=eq." . urlencode($judgeId) . "&WorkID=eq." . urlencode($workId);
            $method = 'PATCH';
        } else {
            // 未評分 → POST 新增
            $url = "$apiUrl/Evaluations";
            $method = 'POST';
            $data = json_encode([
                'JudgeID' => $judgeId,
                'WorkID' => $workId,
                'Score' => (int)$score,
                'Comments' => $comment
            ]);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                "apikey: $apiKey",
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json",
                "Prefer: return=representation"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!in_array($code, [200, 201, 204])) {
            $success = false;
            break;
        }
    }

    echo "<script>alert('" . ($success ? "評分提交成功！" : "提交失敗") . "'); location.href='judge_system.php';</script>";
    exit();
}
?>

<!-- HTML 表單 -->
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>作品評分上傳</title>
    <style>
        body { font-family: Microsoft JhengHei; background-color: #f5f5f5; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: center; }
        textarea { width: 90%; height: 60px; }
        input[type="number"] { width: 70px; }
        button {
            margin-top: 20px;
            padding: 10px 30px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
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
                    <th>作品編號</th><th>簡介</th><th>海報</th><th>影片</th><th>程式碼</th><th>分數</th><th>評論</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($works as $work): 
                    $wid = $work['WorkID'];
                    $scored = isset($evaluatedMap[$wid]);
                    $score = $scored ? $evaluatedMap[$wid]['Score'] : '';
                    $comment = $scored ? $evaluatedMap[$wid]['Comments'] : '';
                ?>
                <tr>
                    <td><?= htmlspecialchars($wid) ?></td>
                    <td><?= htmlspecialchars($work['Description'] ?? '-') ?></td>
                    <td><?= $work['Poster'] ? "<img src='".htmlspecialchars($work['Poster'])."' width='100'>" : '無' ?></td>
                    <td><?= $work['VideoLink'] ? "<a href='".htmlspecialchars($work['VideoLink'])."' target='_blank'>觀看</a>" : '無' ?></td>
                    <td><?= $work['CodeLink'] ? "<a href='".htmlspecialchars($work['CodeLink'])."' target='_blank'>下載</a>" : '無' ?></td>
                    <td><input type="number" name="scores[<?= $wid ?>]" min="0" max="100" required value="<?= htmlspecialchars($score) ?>"></td>
                    <td><textarea name="comments[<?= $wid ?>]" required><?= htmlspecialchars($comment) ?></textarea></td>
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




