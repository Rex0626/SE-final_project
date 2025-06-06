<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

function fetch($table, $filter = '', $select = '*') {
    global $baseUrl, $apiKey;
    $url = $baseUrl . $table . '?select=' . urlencode($select);
    if ($filter) $url .= '&' . $filter;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($res, true);
    if (!is_array($decoded)) {
        echo "<h3 style='color:red'>❌ Supabase 回傳錯誤</h3>";
        echo "<pre>" . htmlspecialchars($res) . "</pre>";
        exit();
    }
    return $decoded;
}

if (!isset($_GET['competition_id'])) {
    $competitions = fetch('Competitions', '', 'Id,Title');
    ?>
    <!DOCTYPE html>
    <html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <title>選擇比賽</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body style="background:#f4f4f9; padding: 30px;">
    <div class="container">
        <h1 class="text-center">🎯 請選擇一個比賽</h1>
        <form method="get" class="text-center">
            <select name="competition_id" class="form-control" style="max-width: 300px; margin: 20px auto;">
                <?php foreach ($competitions as $comp): ?>
                    <option value="<?= $comp['Id'] ?>"><?= htmlspecialchars($comp['Title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">查看評審結果</button>
        </form>
    </div>
    </body>
    </html>
    <?php
    exit();
}

$competition_id = $_GET['competition_id'];

// 資料抓取
$teamWorks = fetch('All-Teams', "CompetitionId=eq.$competition_id", 'TeamName,WorkID');
$works = fetch('Works', '', 'WorkID,Description');
$evaluations = fetch('Evaluations', '', 'WorkID,JudgeID,Score,Comments');
$judges = fetch('Participants', 'Role=eq.Judge', 'ParticipantID,Name');

// 對照表建立
$workMap = [];
foreach ($works as $w) $workMap[$w['WorkID']] = $w['Description'];
$teamMap = [];
foreach ($teamWorks as $tw) $teamMap[$tw['WorkID']] = $tw['TeamName'];
$judgeMap = [];
foreach ($judges as $j) $judgeMap[$j['ParticipantID']] = $j['Name'];

// 過濾
$validWorkIds = array_keys($teamMap);
$evaluations = array_filter($evaluations, fn($e) => in_array($e['WorkID'], $validWorkIds));
?>

<!DOCTYPE html>
<html lang="zh-Hant">

    <head>
        <meta charset="UTF-8">
        <title>評審結果管理</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body {
                background-color: #f4f4f9;
                padding: 30px;
            }
            h1 {
                margin-bottom: 20px;
            }
            table {
                background-color: #ffffff;
            }
            .nav-buttons {
                margin-top: 20px;
                text-align: center;
            }
            .nav-buttons .btn {
                margin: 5px;
            }
        </style>
    </head>

    <body>

        <div class="container">
            <h1 class="text-center">🏆 評審結果管理</h1>

            <!-- 評分總表 -->
            <div id="scoresView">
                <table id="scoreTable" class="table table-striped table-bordered bg-white">
                    <thead class="thead-dark">
                    <tr>
                        <th>隊伍名稱</th>
                        <th>作品名稱</th>
                        <th>評審姓名</th>
                        <th>分數</th>
                        <th>評語</th>
                    </tr>
                    </thead>
                    <tbody id="scoreBody"></tbody>
                </table>
            </div>

            <!-- 平均分數 -->
            <div id="averageView" style="display: none;">
                <table id="avgTable" class="table table-striped table-bordered bg-white">
                    <thead class="thead-dark">
                    <tr>
                        <th>隊伍名稱</th>
                        <th>作品名稱</th>
                        <th>平均分數</th>
                        <th>參與評分人數</th>
                        <th>🏅 排名</th>
                    </tr>
                    </thead>
                    <tbody id="avgBody"></tbody>
                </table>
            </div>
        </div>

        <div class="nav-buttons">
            <button class="btn btn-primary" onclick="switchView('scores')">📋 評分總表</button>
            <button class="btn btn-info" onclick="switchView('average')">📊 平均分數</button>
            <a href="publish_award.php?competition_id=<?= htmlspecialchars($competition_id) ?>" class="btn btn-success">📣 發佈得獎公告</a>
            <a href="./select_competition.php" class="btn btn-secondary">⬅️ 返回</a>
        </div>

        <script>
            const teamMap = <?= json_encode($teamMap, JSON_UNESCAPED_UNICODE); ?>;
            const workMap = <?= json_encode($workMap, JSON_UNESCAPED_UNICODE); ?>;
            const judgeMap = <?= json_encode($judgeMap, JSON_UNESCAPED_UNICODE); ?>;
            const evaluations = <?= json_encode(array_values($evaluations), JSON_UNESCAPED_UNICODE); ?>;

            function renderTables() {
                const scoreBody = document.getElementById('scoreBody');
                const avgBody = document.getElementById('avgBody');
                const avgMap = {};

                scoreBody.innerHTML = '';
                avgBody.innerHTML = '';

                evaluations.forEach(e => {
                    const teamName = teamMap[e.WorkID] ?? '未知隊伍';
                    const workTitle = workMap[e.WorkID] ?? '未知作品';
                    const judgeName = judgeMap[e.JudgeID] ?? '未知評審';
                    const score = (e.Score !== null && e.Score !== '') ? parseFloat(e.Score) : null;
                    const comment = e.Comments || '尚未填寫';

                    scoreBody.innerHTML += `
                        <tr>
                            <td>${teamName}</td>
                            <td>${workTitle}</td>
                            <td>${judgeName}</td>
                            <td>${score !== null ? score : '尚未評分'}</td>
                            <td>
                                ${comment !== '尚未填寫'
                                    ? `<button class='btn btn-success btn-sm' onclick="alert('評語：\\n\\n${comment}')">查看</button>`
                                    : '<span>尚未填寫</span>'}
                            </td>
                        </tr>`;

                    if (score !== null) {
                        if (!avgMap[e.WorkID]) avgMap[e.WorkID] = { total: 0, count: 0 };
                        avgMap[e.WorkID].total += score;
                        avgMap[e.WorkID].count++;
                    }
                });

                const avgList = Object.entries(avgMap).map(([workId, entry]) => {
                    const teamName = teamMap[workId] ?? '未知隊伍';
                    const workTitle = workMap[workId] ?? '未知作品';
                    return {
                        team: teamName,
                        work: workTitle,
                        avg: entry.total / entry.count,
                        count: entry.count
                    };
                });

                avgList.sort((a, b) => b.avg - a.avg);

                let currentRank = 0;
                let lastScore = null;
                let rankCount = 0;

                avgList.forEach(item => {
                    let rankLabel = '--';
                    if (item.avg !== null) {
                        if (lastScore === null || item.avg !== lastScore) {
                            currentRank += rankCount + 1;
                            rankCount = 0;
                        } else {
                            rankCount++;
                        }

                        if (currentRank === 1) rankLabel = '🏆 金牌';
                        else if (currentRank === 2) rankLabel = '🥈 銀牌';
                        else if (currentRank === 3) rankLabel = '🥉 銅牌';

                        lastScore = item.avg;
                    }

                    avgBody.innerHTML += `
                        <tr>
                            <td>${item.team}</td>
                            <td>${item.work}</td>
                            <td>${item.avg.toFixed(2)}</td>
                            <td>${item.count}</td>
                            <td>${rankLabel}</td>
                        </tr>`;
                });
            }

            function switchView(view) {
                document.getElementById('scoresView').style.display = view === 'scores' ? 'block' : 'none';
                document.getElementById('averageView').style.display = view === 'average' ? 'block' : 'none';
            }

            renderTables();
        </script>

    </body>

</html>
