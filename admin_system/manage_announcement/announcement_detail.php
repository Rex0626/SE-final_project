<?php
session_start();

// 如果需要限制只有登入用戶可看，可加驗證，這裡示範公開頁面
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Announcement';

if (!isset($_GET['id'])) {
    die('❌ 未提供公告ID');
}

$id = $_GET['id'];

// 用 curl 從 Supabase 拿詳細資料
$url = $baseUrl . '?select=Title,Content,Year&AnnouncementID=eq.' . $id;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!$data || count($data) === 0) {
    die('❌ 找不到公告內容');
}

$announcement = $data[0];  // 第一筆即是目標公告
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($announcement['Title']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background: #f4f4f9; padding: 30px; font-family: Arial, sans-serif; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        h1 { margin-bottom: 20px; }
        .date { color: gray; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($announcement['Title']) ?></h1>
        <div class="date">發布日期：<?= htmlspecialchars(substr($announcement['Year'], 0, 10)) ?></div>
        <div class="content" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($announcement['Content'])) ?></div>
        <div class="mt-4">
            <a href="../../main.html" class="btn btn-secondary">返回首頁</a>
        </div>
    </div>
</body>
</html>
