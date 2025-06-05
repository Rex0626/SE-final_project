<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
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
    $data = json_decode($res, true);
    if (!is_array($data)) {
        echo "<pre>❌ Supabase API 回傳錯誤：\n";
        var_dump($res);
        exit();
    }
    return $data;
}

// 抓 All-Teams 資料
$teams = fetch('All-Teams', 'TeamName,CompetitionYear,WorkID,TeamID');

// 抓 Participants 資料
$participants = fetch('Participants', 'ParticipantID,Name');

// 建立 Teacher 對照表
$teacherMap = [];
foreach ($participants as $p) {
    $teacherMap[$p['ParticipantID']] = $p['Name'];
}

// 抓 Works 資料
$works = fetch('Works', 'WorkID,Description');

// 建立 WorkID → Description 對照表
$workMap = [];
foreach ($works as $w) {
    $workMap[$w['WorkID']] = $w['Description'];
}

?>


<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>隊伍資料總覽</title>
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="../../styles.css">
        <style>
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 8px; border: 1px solid #ccc; text-align: left; }
            th { cursor: pointer; }
            input[type="text"] { padding: 6px; width: 200px; margin-bottom: 10px; }
        </style>
    </head>

    <body>
        <header>
            <h1>高雄大學激發學生創意競賽管理系統</h1>
            <h1>隊伍資料總覽</h1>
            <p>歡迎，<?php echo htmlspecialchars($_SESSION['email']); ?>！</p>
        </header>

        <table>
            <thead>
                <tr>
                    <th>隊伍名稱</th>
                    <th>參賽年份</th>
                    <!--<th>作品 ID</th>-->
                    <th>作品描述</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $row): ?>
                    <tr>
                        <td>
                            <a href="./team_detail.php?team_id=<?= urlencode($row['TeamID']) ?>">
                            <?= htmlspecialchars($row['TeamName']) ?>
                            </a>
                        </td>

                        <td><?= htmlspecialchars($row['CompetitionYear']) ?></td>
                        <!--<td><?= htmlspecialchars($row['WorkID'] ?? '（無）') ?></td>-->
                        
                        <td>
                        <?php if (!empty($row['WorkID']) && isset($workMap[$row['WorkID']])): ?>
                            <a href="./work_detail.php?work_id=<?= urlencode($row['WorkID']) ?>">
                            ✅ <?= htmlspecialchars($workMap[$row['WorkID']]) ?>
                            </a>
                        <?php else: ?>
                            ❌ 尚未上傳
                        <?php endif; ?>
                        </td>
                    
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

            <button onclick="history.back()" style="font-size:14px; padding:4px 10px;">← 返回</button>
    </body>


    <footer>
        <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
    </footer>

    <script src="../../scripts.js"></script>


</html>


