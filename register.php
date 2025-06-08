<?php
session_start();

// 接收表單資料
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = strtolower(trim($_POST['role'] ?? ''));

// 驗證欄位
if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($role)) {
    echo "<p style='color:red;'>❌ 所有欄位皆為必填</p>";
    exit();
}

// 只允許三種角色註冊
$validRoles = ['student', 'teacher', 'judge'];
if (!in_array($role, $validRoles)) {
    echo "<p style='color:red;'>❌ 非法角色：$role</p>";
    exit();
}

// Supabase 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// Step 1: 檢查帳號是否已存在
$checkUrl = $apiUrl . '?Email=eq.' . urlencode($email);
$ch = curl_init($checkUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 顯示連線狀態
if ($httpcode === 200) {
    echo "<p style='color:green;'>✅ 成功連接 Supabase 資料庫</p>";
} else {
    echo "<p style='color:red;'>❌ 資料庫連線失敗，HTTP 狀態碼：$httpcode</p>";
    echo "<pre>$response</pre>";
    exit();
}

// 檢查是否已存在該帳號
$existing = json_decode($response, true);
if (is_array($existing) && count($existing) > 0) {
    echo "<p style='color:red;'>❌ 帳號已存在，請使用其他 Email</p>";
    exit();
}

// Step 2: 加密密碼
$hashedPassword = $password;

// Step 3: 建立新使用者資料
$data = [
    'Name' => $name,
    'Email' => $email,
    'Phone' => $phone,
    'Password' => $hashedPassword,
    'Role' => ucfirst($role),  // 儲存時開頭大寫（Student, Teacher, Judge）
];

$jsonData = json_encode($data);

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Step 4: 顯示新增結果
if ($httpcode === 201) {
    echo "<p style='color:green;'>✅ 註冊成功</p>";
    echo "<meta http-equiv='refresh' content='2;url=login.html'>";
} else {
    echo "<p style='color:red;'>❌ 註冊失敗，HTTP 狀態碼：$httpcode</p>";
    echo "<pre>$response</pre>";
}
?>
