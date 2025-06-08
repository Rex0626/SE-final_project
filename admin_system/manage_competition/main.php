<?php
session_start();

// ✅ 只有系統管理員能使用
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Competitions';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// 取得比賽資料
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl . '?select=*');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey
));
$response = curl_exec($ch);
curl_close($ch);

$competitions = json_decode($response, true);

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
                background-color: #f4f4f9;
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
                background-color: #3498db;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-size: 13px;
            }
            .btn:hover {
                background-color: #2980b9;
            }
            .btn-danger {
                background-color: #e74c3c;
            }
            .btn-danger:hover {
                background-color: #c0392b;
            }
        </style>

    </head>

    <body>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
            <div class="alert alert-success text-center">
                ✅ 比賽已成功刪除！
            </div>
        <?php endif; ?>

        <div class="header-bar">
            <h2>比賽資訊管理</h2>
            <a href="../admin_system.php" class="btn">🔙 回到管理員系統</a>
        </div>

        <a href="add.php" class="btn">➕ 新增比賽</a>
        <br><br>

    <table>
        <thead>
            <tr>
                <th>標題</th>
                <th>海報</th>
                <th>起始日期</th>
                <th>結束日期</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($competitions as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['Title']) ?></td>

                    <td>
                        <?php if (!empty($c['Poster'])): ?>
                            <button class="btn" onclick="showPoster('<?= htmlspecialchars($c['Poster']) ?>')">🖼️ 預覽</button>
                        <?php else: ?>
                            <span style="color: #888;">尚未上傳</span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($c['Start_date']) ?></td>
                    <td><?= htmlspecialchars($c['End_date']) ?></td>

                    <td>
                        <a href="./edit.php?id=<?= $c['Id'] ?>" class="btn">✏️ 編輯</a>
                        <a href="./delete.php?id=<?= $c['Id'] ?>" class="btn btn-danger"
                        onclick="return confirm('❗確定要刪除這場比賽嗎？此動作無法復原')">🗑️ 刪除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ✅ 圖片預覽 Modal -->
    <div class="modal fade" id="posterModal" tabindex="-1" role="dialog" aria-labelledby="posterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">比賽海報預覽</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="關閉">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body text-center">
            <img id="posterImage" src="" alt="海報圖片" style="max-width:100%; max-height:70vh;">
        </div>
        </div>
    </div>
    </div>

    <!-- ✅ JS：引入 Bootstrap + Modal 控制 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
    function showPoster(url) {
        document.getElementById('posterImage').src = url;
        $('#posterModal').modal('show');
    }
    </script>

    </body>

</html>