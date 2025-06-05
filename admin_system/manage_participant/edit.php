<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "❌ 缺少 ID";
    exit();
}

// Supabase 設定
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        "Name" => $_POST['name'],
        "Email" => $_POST['email'],
        "Phone" => $_POST['phone'],
        "Role" => $_POST['role']
    ];
    if (!empty($_POST['password'])) {
        $data['Password'] = $_POST['password']; // 若有登入功能建議使用 password_hash
    }

    $url = $baseUrl . '?ParticipantID=eq.' . urlencode($id);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        header("Location: main.php");
        exit();
    } else {
        $error = [
            'code' => $httpcode,
            'response' => $response
        ];
    }
} else {
    $url = $baseUrl . '?ParticipantID=eq.' . urlencode($id);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $judges = json_decode($response, true);
    $judge = $judges[0] ?? null;

    if (!$judge) {
        echo "❌ 查無此評審";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯參與者帳號</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            padding-top: 50px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 450px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>編輯參與者帳號</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading">❌ 更新失敗</h5>
                <p><strong>HTTP 狀態碼：</strong> <?= $error['code'] ?></p>
                <p><strong>API 回應：</strong></p>
                <pre><?= htmlspecialchars($error['response']) ?></pre>
            </div>
        <?php endif; ?>

        <form action="edit.php?id=<?= urlencode($id) ?>" method="POST">
            <div class="form-group">
                <label for="name">姓名</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($judge['Name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">電子郵件</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($judge['Email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">電話</label>
                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($judge['Phone']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">密碼（若不更改可留空）</label>
                <input type="password" class="form-control" name="password">
            </div>

            <div class="form-group">
                <label for="role">角色</label>
                <select class="form-control" name="role" required>
                    <option value="Student" <?= $judge['Role'] === 'Student' ? 'selected' : '' ?>>學生</option>
                    <option value="Teacher" <?= $judge['Role'] === 'Teacher' ? 'selected' : '' ?>>老師</option>
                    <option value="Judge" <?= $judge['Role'] === 'Judge' ? 'selected' : '' ?>>評審</option>
                    <option value="Admin" <?= $judge['Role'] === 'Admin' ? 'selected' : '' ?>>管理員</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">更新</button>
        
        </form>

        <div class="text-center mt-3">
            <a href="./main.php" class="btn btn-secondary">返回參與者管理介面</a>
        </div>
    </div>
</body>
</html>
