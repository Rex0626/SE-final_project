<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "❌ 缺少公告 ID";
    exit();
}

// Supabase 設定
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Announcement';

$error = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $yearInput = $_POST['year'];
    $year = strlen($yearInput) === 4 ? $yearInput . '-01-01' : $yearInput;

    $data = [
    "Title" => $_POST['title'],
    "Content" => $_POST['content'],
    "Year" => $year  // ← 套用上面處理過的 $year
    ];

    $url = $baseUrl . '?AnnouncementID=eq.' . urlencode($id);
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
        header("Location: ./main.php");
        exit();
    } else {
        $error = [
            'code' => $httpcode,
            'response' => $response
        ];
    }
} else {
    // 取得原始公告資料
    $url = $baseUrl . '?AnnouncementID=eq.' . urlencode($id);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $announcements = json_decode($response, true);
    $announcement = $announcements[0] ?? null;

    if (!$announcement) {
        echo "❌ 查無此公告";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯公告</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            padding-top: 50px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 550px;
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
        <h2>編輯公告</h2>

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
                <label for="title">公告標題</label>
                <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($announcement['Title']) ?>" required>
            </div>
            <div class="form-group">
                <label for="content">公告內容</label>
                <textarea class="form-control" name="content" rows="6" required><?= htmlspecialchars($announcement['Content']) ?></textarea>
            </div>
            <div class="form-group">
                <label for="year">年度</label>
                <input type="date" class="form-control" name="year" value="<?= htmlspecialchars($announcement['Year']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">更新</button>
        </form>

        <div class="text-center mt-3">
            <a href="main.php" class="btn btn-secondary">返回公告管理</a>
        </div>
    </div>
</body>
</html>
