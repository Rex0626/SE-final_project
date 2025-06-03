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

$apiUrl = "https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants"
         . "?Role=eq.Judge&select=ParticipantID,Name,Email,Phone"
         . "&limit=$perPage&offset=$offset";

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

$judges = [];
if ($httpcode === 200) {
    $judges = json_decode($response, true);
} else {
    echo "<p style='color:red;'>❌ 無法載入評審資料，HTTP 狀態碼：$httpcode</p>";
    exit();
}

// 查總筆數
$countUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants?Role=eq.Judge&select=ParticipantID';
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
    $allJudges = json_decode($countResponse, true);
    $totalItems = is_array($allJudges) ? count($allJudges) : 0;
}
$totalPages = ceil($totalItems / $perPage);
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理評審資料</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../../styles.css">

    <!-- 建議加上 Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

</head>

<body>
    <header>
        <h1>高雄大學激發學生創意競賽管理系統</h1>
        <h1>管理評審資料</h1>
        <p>歡迎，<?php echo htmlspecialchars($_SESSION['name']); ?>！</p>
    </header>

    <main class="container mt-4">
        <h2 class="mb-4">評審資料清單</h2>

        <?php if ($totalPages > 1): ?>
            <p class="text-center">📄 第 <?= $page ?> 頁 / 共 <?= $totalPages ?> 頁</p>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>姓名</th>
                        <th>Email</th>
                        <th>電話</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($judges as $judge): ?>
                    <tr>
                        <td><?= htmlspecialchars($judge['Name']) ?></td>
                        <td><?= htmlspecialchars($judge['Email']) ?></td>
                        <td><?= htmlspecialchars($judge['Phone']) ?></td>
                        <td>
                            <a href="./edit.php?id=<?= $judge['ParticipantID'] ?>" class="btn btn-sm btn-info">編輯</a>
                            <a href="./delete.php?id=<?= $judge['ParticipantID'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete();">刪除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="./add.php" class="btn btn-success">➕ 新增評審</a>
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
            <a href="../admin_system.php" class="btn btn-success">⬅️ 返回</a>
        </div>

    </main>

    <footer>
        <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
    </footer>

    <script src="../../scripts.js"></script>

    <script>
    function confirmDelete() {
        return confirm('⚠️ 確定要刪除此評審嗎？此操作無法還原！') &&
            confirm('請再次確認，是否確定刪除？');
    }
    </script>


</body>
</html>
