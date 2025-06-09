<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$keyword = $_GET['keyword'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$perPage = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$filterParts = [];

if (!empty($keyword)) {
    $encoded = urlencode("%$keyword%");
    $filterParts[] = "or=(Name.ilike.$encoded,Email.ilike.$encoded)";
}
if (!empty($roleFilter)) {
    $filterParts[] = "Role=eq.$roleFilter";  
}

$filterQuery = $filterParts ? '&' . implode('&', $filterParts) : '';

$apiUrl = "https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants"
         . "?select=ParticipantID,Name,Email,Password,Phone,Role"
         . "$filterQuery"
         . "&limit=$perPage&offset=$offset";

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$participants = [];
if ($httpcode === 200) {
    $participants = json_decode($response, true);
} else {
    echo "<p style='color:red;'>❌ 無法載入參與者資料，HTTP 狀態碼：$httpcode</p>";
    exit();
}

// 查總筆數
$countUrl = "https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants?select=ParticipantID$filterQuery";
$ch2 = curl_init($countUrl);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
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
        <title>比賽資訊管理</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

        <style>
            body {
                font-family: Arial;
                background-color: #FFF9F4;
                padding: 20px;
                color: #333;
            }
            h2 {
                color: #2c3e50;
            }
            .header-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                background: #fff;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                border-radius: 6px;
                overflow: hidden;
            }
            th, td {
                padding: 14px 16px;
                border-bottom: 1px solid #e0e0e0;
                text-align: left;
            }
            th {
                background-color: #2c3e50;
                color: white;
            }
            tr:hover {
                background-color: #f0f0f5;
            }
            .btn {
                padding: 6px 10px;
                color: white;
                background-color: #B39870;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-size: 13px;
            }
            .btn-danger {
                background-color: #e74c3c;
            }
            .btn-danger:hover {
                background-color: #c0392b;
            }
        .btn:hover{
            background: #FFF9F4;
            box-shadow: 0px 2px 10px 5px #E1C6A8;
            color: #221F1F;
            border: none;
        }
        .btn:active {
            color: #000; 
        }
        .btn:active:after {
            background: transparent;
        }
        .btn:hover:before {
            opacity: 1;
        }
        </style>

    </head>

    <body>
        <header>
            <h2 class="text-center">參與者資料管理</h2>
            <p class="text-center">歡迎，<?= htmlspecialchars($_SESSION['name']) ?>！</p>
        </header>

        <main class="container mt-4">
            <form method="GET" class="form-inline mb-4 justify-content-center">
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control mr-2" placeholder="搜尋姓名或 Email">
                <select name="role" class="form-control mr-2">
                    <option value="">全部角色</option>
                    <option value="Student" <?= $roleFilter === 'Student' ? 'selected' : '' ?>>學生</option>
                    <option value="Teacher" <?= $roleFilter === 'Teacher' ? 'selected' : '' ?>>老師</option>
                    <option value="Judge" <?= $roleFilter === 'Judge' ? 'selected' : '' ?>>評審</option>
                    <option value="Admin" <?= $roleFilter === 'Admin' ? 'selected' : '' ?>>管理員</option>
                </select>
                <button type="submit" class="btn btn-primary">搜尋</button>
            </form>

            <?php if ($totalPages > 1): ?>
                <p class="text-center">第 <?= $page ?> 頁 / 共 <?= $totalPages ?> 頁</p>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th style="background: #221F1F;">姓名</th>
                            <th style="background: #221F1F;">Email</th>
                            <th style="background: #221F1F;">電話</th>
                            <th style="background: #221F1F;">角色</th>
                            <th style="background: #221F1F;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['Name']) ?></td>
                            <td><?= htmlspecialchars($participant['Email']) ?></td>
                            <td><?= htmlspecialchars($participant['Phone']) ?></td>
                            <td><?= htmlspecialchars($participant['Role']) ?></td>
                            <td>
                                <a href="./edit.php?id=<?= $participant['ParticipantID'] ?>" class="btn btn-sm btn-info">編輯</a>
                                <a href="./delete.php?id=<?= $participant['ParticipantID'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete();">刪除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <a href="./add.php" class="btn btn-success">➕ 新增參與者</a>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="text-center mt-3">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&keyword=<?= urlencode($keyword) ?>&role=<?= urlencode($roleFilter) ?>" class="btn btn-outline-primary">上一頁</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&keyword=<?= urlencode($keyword) ?>&role=<?= urlencode($roleFilter) ?>" class="btn btn-outline-primary">下一頁</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="../admin_system.php" class="btn btn-success">返回</a>
            </div>
        </main>

        <script>
            function confirmDelete() {
                return confirm('⚠️ 確定要刪除此參與者嗎？此操作無法還原！');
            }
        </script>

    </body>

</html>