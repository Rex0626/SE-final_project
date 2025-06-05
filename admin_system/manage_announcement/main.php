<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$perPage = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = "https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Announcement";

// 取得公告資料
$apiUrl = "$baseUrl?select=AnnouncementID,Admin_Email,Title,Content,Year&limit=$perPage&offset=$offset";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$announcements = [];
if ($httpcode === 200) {
    $announcements = json_decode($response, true);
} else {
    echo "<p style='color:red;'>❌ 無法載入公告資料，HTTP 狀態碼：$httpcode</p>";
    exit();
}

// 查總筆數
$countUrl = "$baseUrl?select=AnnouncementID";
$ch2 = curl_init($countUrl);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$countResponse = curl_exec($ch2);
curl_close($ch2);

$totalItems = 0;
if ($countResponse) {
    $allData = json_decode($countResponse, true);
    $totalItems = is_array($allData) ? count($allData) : 0;
}
$totalPages = ceil($totalItems / $perPage);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>管理公告資料</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f4f9; font-family: Arial, sans-serif; }
        .container { padding-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">📢 公告管理</h2>

        <?php if ($totalPages > 1): ?>
            <p class="text-center">📄 第 <?= $page ?> 頁 / 共 <?= $totalPages ?> 頁</p>
        <?php endif; ?>

        <form method="POST" action="publish_selected_to_homepage.php">
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th><input type="checkbox" onclick="toggleAll(this)"></th>
                        <th>標題</th>
                        <th>年份</th>
                        <th>上傳者 Email</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($announcements as $a): ?>
                    <tr>
                        <td><input type="checkbox" name="selected[]" value="<?= $a['AnnouncementID'] ?>"></td>
                        <td><?= htmlspecialchars($a['Title']) ?></td>
                        <td><?= htmlspecialchars($a['Year']) ?></td>
                        <td><?= htmlspecialchars($a['Admin_Email']) ?></td>
                        <td>
                            <a href="./edit.php?id=<?= $a['AnnouncementID'] ?>" class="btn btn-info btn-sm">編輯</a>
                            <a href="./delete.php?id=<?= $a['AnnouncementID'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete();">刪除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary" onclick="return confirm('確定要將所選公告發佈到首頁？')">📢 發佈到首頁</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="./add.php" class="btn btn-success">➕ 新增公告</a>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="text-center mt-3">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline-primary">⬅️ 上一頁</a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline-primary">下一頁 ➡️</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="../admin_system.php" class="btn btn-secondary">⬅️ 返回主選單</a>
        </div>

    </div>

    <script>

        function confirmDelete() {
            return confirm('⚠️ 確定要刪除此公告嗎？此操作無法還原！');
        }

        function toggleAll(source) {
            document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = source.checked);
        }

    </script>
</body>
</html>
