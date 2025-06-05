<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';


// 共用函數：資料抓取
function fetch($table, $filter = '', $select = '*') {
    global $baseUrl, $apiKey;
    $url = $baseUrl . $table . '?select=' . urlencode($select);
    if ($filter) $url .= '&' . $filter;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// 📤 若表單送出，就發佈公告
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $year = $_POST['year'];
    $adminEmail = $_SESSION['email'];

    $data = [
        'Admin_Email' => $adminEmail,
        'Title' => $title,
        'Content' => $content,
        'Year' => $year
    ];

    $ch = curl_init($baseUrl . 'Announcement');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey
    ]);
    $res = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 201) {
        echo "<script>alert('✅ 公告已成功上傳，請到公告管理發佈公告'); window.location.href = 'main.php';</script>";
        exit();
    } else {
        echo "<h3 style='color:red'>❌ 上傳失敗（HTTP $httpcode）</h3>";
        echo "<pre>🔴 回傳內容：" . htmlspecialchars($res) . "</pre>";
    }
}

// 🔎 自動生成公告內容
$evaluations = fetch('Evaluations', '', 'WorkID,Score');
$works = fetch('Works', '', 'WorkID,Description');

// 平均分數
$workMap = [];
foreach ($works as $w) $workMap[$w['WorkID']] = $w['Description'];

$workScores = [];
foreach ($evaluations as $e) {
    if (is_numeric($e['Score'])) {
        $wid = $e['WorkID'];
        if (!isset($workScores[$wid])) $workScores[$wid] = ['sum' => 0, 'count' => 0];
        $workScores[$wid]['sum'] += $e['Score'];
        $workScores[$wid]['count']++;
    }
}

$avgList = [];
foreach ($workScores as $wid => $val) {
    $avg = $val['sum'] / $val['count'];
    $avgList[] = ['work' => $workMap[$wid] ?? '未知作品', 'avg' => $avg];
}
usort($avgList, fn($a, $b) => $b['avg'] <=> $a['avg']);

// 前三名（並列）
$ranked = [];
$currentRank = 1;
$lastScore = null;
$rankCount = 0;
foreach ($avgList as $item) {
    if ($lastScore === null || $item['avg'] !== $lastScore) {
        $currentRank += $rankCount;
        $rankCount = 1;
    } else {
        $rankCount++;
    }
    if ($currentRank > 3) break;
    $ranked[$currentRank][] = $item;
    $lastScore = $item['avg'];
}

// 組合內容
$title = "📢 競賽得獎名單公告";
$year = date('Y') . "-01-01";
$content = "📢 競賽得獎公告（" . date('Y') . "）\n\n";
foreach ($ranked as $rank => $group) {
    $icon = $rank === 1 ? '🥇 第一名：' : ($rank === 2 ? '🥈 第二名：' : '🥉 第三名：');
    $names = array_map(fn($g) => $g['work'] . "（" . round($g['avg'], 2) . "分）", $group);
    $content .= $icon . implode("、", $names) . "\n";
}
$content .= "\n🎉 恭喜以上作品！完整成績請參考系統。";
?>

<!-- ✅ HTML 區塊：可編輯公告內容 -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>發佈得獎公告</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background: #f4f4f9; padding-top: 50px; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">🎉 發佈競賽公告</h2>
        <form method="POST">
            <div class="form-group">
                <label>公告標題</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
            </div>
            <div class="form-group">
                <label>公告內容</label>
                <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($content) ?></textarea>
            </div>
            <div class="form-group">
                <label>年度</label>
                <input type="date" name="year" class="form-control" value="<?= $year ?>" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-success">📤 上傳公告</button>
                <a href="./main.php" class="btn btn-secondary ml-2">返回</a>
            </div>
        </form>
    </div>
</body>
</html>
