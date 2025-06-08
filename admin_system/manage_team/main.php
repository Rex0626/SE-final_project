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

// 抓資料
$teams = fetch('All-Teams', 'TeamName,WorkID,TeamID,CompetitionId');
$works = fetch('Works', 'WorkID,Description');
$competitions = fetch('Competitions', 'Id,Title,Start_date,End_date');

// 建立對照表
$workMap = [];
foreach ($works as $w) {
    $workMap[$w['WorkID']] = $w['Description'];
}

$competitionDateMap = [];
$competitionDeadlineMap = [];
foreach ($competitions as $c) {
    $competitionTitleMap[$c['Id']] = $c['Title'];
    $competitionDateMap[$c['Id']] = date('Y年n月j日', strtotime($c['Start_date']));
    $competitionDeadlineMap[$c['Id']] = date('Y年n月j日', strtotime($c['End_date']));
}

?>

<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <title>隊伍資料總覽</title>
        <style>
            body {
                font-family: Arial;
                background-color: #f4f4f9;
                padding: 20px;
                color: #333;
            }

            a {
                text-decoration: none;
                color: #3498db;
            }

            a:hover {
                text-decoration: underline;
                color: #2980b9;
            }
            h1 {
                text-align: center;
                color: #2c3e50;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                background: #fff;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                border-radius: 6px;
                margin-top: 30px;
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
                padding: 8px 12px;
                color: white;
                background-color: #3498db;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                text-decoration: none;
                font-size: 14px;
            }
            .btn:hover {
                background-color: #2980b9;
            }
            footer {
                margin-top: 40px;
                text-align: center;
                color: #777;
                font-size: 14px;
            }
        </style>
            
    </head>

    <body>
        <h1>隊伍資料總覽</h1>
        <p style="text-align:center;">歡迎，<?= htmlspecialchars($_SESSION['email']) ?>！</p>

        <div style="text-align: center; margin-bottom: 20px;">
            <input type="text" id="searchInput" placeholder="🔍 輸入隊伍名稱或比賽標題搜尋" style="padding: 8px; width: 300px; font-size: 14px;">
        </div>

        <table>
            <thead>
                <tr 
                    data-team="<?= htmlspecialchars($row['TeamName']) ?>" 
                    data-competition="<?= isset($competitionTitleMap[$row['CompetitionId']]) ? htmlspecialchars($competitionTitleMap[$row['CompetitionId']]) : '' ?>"
                >
                    <th>隊伍名稱</th>
                    <th>參加比賽</th>
                    <th>參賽開始日</th>
                    <th>截止日期</th>   
                    <th>作品描述</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $row): ?>
                    <tr 
                        data-team="<?= htmlspecialchars($row['TeamName']) ?>" 
                        data-competition="<?= isset($competitionTitleMap[$row['CompetitionId']]) ? htmlspecialchars($competitionTitleMap[$row['CompetitionId']]) : '' ?>"
                    >
                        <td>
                            <a href="./team_detail.php?team_id=<?= urlencode($row['TeamID']) ?>">
                                <?= htmlspecialchars($row['TeamName']) ?>
                            </a>
                        </td>

                        <td>
                            <?= isset($competitionTitleMap[$row['CompetitionId']]) 
                                ? htmlspecialchars($competitionTitleMap[$row['CompetitionId']]) 
                                : '<span style="color:#888;">（未指定）</span>' ?>
                        </td>

                        <td>
                            <?= isset($competitionDateMap[$row['CompetitionId']])
                                ? htmlspecialchars($competitionDateMap[$row['CompetitionId']])
                                : '<span style="color:#888;">--</span>' ?>
                        </td>

                        <td>
                            <?= isset($competitionDeadlineMap[$row['CompetitionId']])
                                ? htmlspecialchars($competitionDeadlineMap[$row['CompetitionId']])
                                : '<span style="color:#888;">--</span>' ?>
                        </td>


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

        <div style="text-align:center; margin-top:20px;">
            <a href="../admin_system.php" class="btn">← 返回管理員系統</a>
        </div>

        <script>
        document.getElementById("searchInput").addEventListener("keyup", function() {
            const keyword = this.value.toLowerCase();
            const rows = document.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const team = row.dataset.team.toLowerCase();
                const competition = row.dataset.competition.toLowerCase();

                if (team.includes(keyword) || competition.includes(keyword)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
        </script>

        <footer>
            <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
        </footer>
    </body>

</html>
