<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// ✅ 確保 UUID 不為空，並轉小寫處理大小寫敏感問題
$id = strtolower(trim($_GET['id'] ?? ''));
if (!$id) {
    echo "❌ 未提供 ID";
    exit();
}

// ✅ Supabase 設定
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';

// ✅ 構造完整查詢 URL
$url = $baseUrl . '?ParticipantID=eq.' . urlencode($id);

// ✅ DEBUG 印出，放在 URL 定義之後！
echo "<p>🔍 你要刪除的 ParticipantID 是：<strong>$id</strong></p>";
echo "<p>📎 API 呼叫網址：<code>$url</code></p>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
    // ❌ 不要加 'Prefer: return=representation'
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ✅ 判斷結果
if ($httpcode === 204) {
    header("Location: main.php");
    exit();
} else {
    echo "<p style='color:red;'>❌ 刪除失敗，HTTP 狀態碼：$httpcode</p>";
    echo "<p>🔁 回傳內容：<pre>$response</pre></p>";
}
?>
