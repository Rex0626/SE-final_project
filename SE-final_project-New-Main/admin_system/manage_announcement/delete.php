<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// ✅ 取得公告 ID
$id = strtolower(trim($_GET['id'] ?? ''));
if (!$id) {
    echo "❌ 未提供公告 ID";
    exit();
}

// ✅ Supabase 設定
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Announcement';

// ✅ 構造查詢 URL
$url = $baseUrl . '?AnnouncementID=eq.' . urlencode($id);

// ✅ DEBUG 印出資訊（可選）
echo "<p>🔍 將刪除公告 ID：<strong>$id</strong></p>";
echo "<p>📎 API 呼叫網址：<code>$url</code></p>";

// ✅ 發送 DELETE 請求
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ 判斷回傳狀態碼
if ($httpcode === 204) {
    header("Location: main.php");  // 刪除成功 → 回公告列表
    exit();
} else {
    echo "<p style='color:red;'>❌ 刪除失敗，HTTP 狀態碼：$httpcode</p>";
    echo "<pre>🔁 回傳內容：" . htmlspecialchars($response) . "</pre>";
}
?>
