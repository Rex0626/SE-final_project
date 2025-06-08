<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

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
    return json_decode($res, true);
}

$competitions = fetch('Competitions', 'Id,Title,Start_date');
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>比賽清單</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body style="background-color:#f4f4f9; padding: 30px;">
    <div class="container">
        <h1 class="text-center mb-4">📅 比賽清單</h1>
        <table class="table table-striped table-bordered bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>比賽名稱</th>
                    <th>開始日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competitions as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['Title']) ?></td>
                        <td><?= date('Y/m/d', strtotime($c['Start_date'])) ?></td>
                        <td>
                            <a href="award_summary.php?competition_id=<?= $c['Id'] ?>" class="btn btn-primary btn-sm">
                                查看得獎結果
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="../admin_system.php" class="btn btn-secondary mt-3">⬅️ 返回管理首頁</a>
    </div>
</body>
</html>
