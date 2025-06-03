<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// 基本設定
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = '你的API金鑰';  // ✅ 請確定這是 anon 的 key

function fetch($table, $select = '*') {
    global $baseUrl, $apiKey;
    $url = $baseUrl . $table . '?select=' . urlencode($select);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);

    // ❗ 防錯：不是陣列 → 顯示錯誤
    if (!is_array($data)) {
        echo "<pre>❌ API 回傳錯誤：\n";
        var_dump($res);
        echo "</pre>";
        exit();
    }

    return $data;
}

// 抓 All-Teams、Participants、Works
$teams = fetch('All-Teams', 'TeamName,CompetitionYear,TeacherID,WorkID');
$participants = fetch('Participants', 'ParticipantID,Name');
$works = fetch('Works', 'WorkID,Title');

// 對應表建立
$teacherMap = [];
foreach ($participants as $p) {
    $teacherMap[$p['ParticipantID']] = $p['Name'];
}
$workMap = [];
foreach ($works as $w) {
    $workMap[$w['WorkID']] = $w['Title'];
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>隊伍總覽</title>
</head>
<body>
<h2>隊伍資訊總覽</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>隊伍名稱</th>
        <th>指導老師</th>
        <th>參賽時間</th>
        <th>作品狀態</th>
    </tr>
    <?php foreach ($teams as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['TeamName']) ?></td>
            <td><?= htmlspecialchars($teacherMap[$row['TeacherID']] ?? '尚未指定') ?></td>
            <td><?= htmlspecialchars($row['CompetitionYear']) ?></td>
            <td>
                <?= isset($row['WorkID']) && isset($workMap[$row['WorkID']])
                    ? '✅ ' . htmlspecialchars($workMap[$row['WorkID']])
                    : '❌ 尚未上傳' ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
