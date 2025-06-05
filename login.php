<?php
session_start();

// Step 1: 接收帳密與預期角色（小寫標準化）
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$expectedRole = strtolower($_POST['role'] ?? '');

// 定義合法角色（全小寫）
$validRoles = ['student', 'admin', 'teacher', 'judge'];

// 驗證角色是否合法
if (!in_array($expectedRole, $validRoles)) {
    echo "<p style='color:red;'>❌ 非法進入登入頁面，請從正確子系統進入</p>";
    echo "<p>⏳ 5 秒後將自動返回主網頁...</p>";
    echo "<script>setTimeout(() => { window.location.href = 'main.html'; }, 5000);</script>";
    exit();
}

echo "<h3>🔍 登入流程除錯輸出</h3>";
echo "<p>📨 輸入帳號：<strong>$email</strong></p>";
echo "<p>🎯 預期子系統角色：<strong>$expectedRole</strong></p>";

// Step 2: 查詢 Supabase
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$url = $apiUrl . '?Email=eq.' . urlencode($email);

// 呼叫 API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 顯示是否連線成功
if ($httpcode === 200) {
    echo "<p style='color:green;'>✅ 成功連接 Supabase 資料庫</p>";
} else {
    echo "<p style='color:red;'>❌ 連接 Supabase 失敗，HTTP 狀態碼：$httpcode</p>";
    echo "<pre>$response</pre>";
    echo "<p>⏳ 5 秒後將自動返回主網頁...</p>";
    echo "<script>setTimeout(() => { window.location.href = 'main.html'; }, 5000);</script>";
    exit();
}

// Step 3: 處理回傳結果
$users = json_decode($response, true);

if (is_array($users) && count($users) === 1) {
    echo "<p style='color:green;'>✅ 查到帳號</p>";
    $user = $users[0];

    $rawRole = $user['Role'] ?? '';
    $actualRole = strtolower($rawRole);
    $hashedPassword = $user['Password'] ?? '';

    echo "<p>📦 資料庫帳號角色：<strong>$rawRole</strong></p>";

    // Step 4: 密碼驗證
    if ($password === $hashedPassword) {
        echo "<p style='color:green;'>✅ 密碼正確</p>";

        // Step 5: 角色比對
        if ($actualRole !== $expectedRole) {
            echo "<p style='color:red;'>❌ 角色錯誤</p>";
            echo "<p>🔑 您的角色為：<strong>$rawRole</strong>，無法登入子系統：<strong>$expectedRole</strong></p>";
            echo "<p>⏳ 5 秒後將自動返回主網頁...</p>";
            echo "<script>setTimeout(() => { window.location.href = 'main.html'; }, 5000);</script>";
            exit();
        }

        // Step 6: 登入成功
        echo "<p style='color:green;'>✅ 角色正確，登入成功</p>";
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $actualRole;
        $_SESSION['name'] = $user['Name'];

        // Step 7: 導向
        switch ($actualRole) {
            case 'admin':
                header('Location: ./admin_system/admin_system.php');
                break;
            case 'student':
                header('Location: ./student_system/student_system.html');
                break;
            case 'teacher':
                header('Location: ./teacher_system/teacher_system.html');
                break;
            case 'judge':
                header('Location: ./judge_system/judge_system.html');
                break;
        }
        exit();
    } else {
        echo "<p style='color:red;'>❌ 密碼錯誤</p>";
        echo "<p>⏳ 5 秒後將自動返回主網頁...</p>";
        echo "<script>setTimeout(() => { window.location.href = 'main.html'; }, 5000);</script>";
    }
} else {
    echo "<p style='color:red;'>❌ 查無此帳號</p>";
    echo "<pre>$response</pre>";
    echo "<p>⏳ 5 秒後將自動返回主網頁...</p>";
    echo "<script>setTimeout(() => { window.location.href = 'main.html'; }, 5000);</script>";
}
?>
