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
        echo "<p>🔗 查詢網址：<code>$url</code></p>";
        echo "<pre>" . htmlspecialchars($res) . "</pre>";
        exit();
    }

    return $decoded;
}

$evaluations = fetch('Evaluations', '', 'WorkID,JudgeID,Score,Comments');
$works = fetch('Works', '', 'WorkID,Description');
$judges = fetch('Participants', 'Role=eq.Judge', 'ParticipantID,Name');

$workMap = [];
foreach ($works as $w) $workMap[$w['WorkID']] = $w['Description'];

$judgeMap = [];
foreach ($judges as $j) $judgeMap[$j['ParticipantID']] = $j['Name'];

// 計算平均
$workScores = []; // work_id => ['sum' => ..., 'count' => ...]
foreach ($evaluations as $e) {
    if (isset($e['Score']) && is_numeric($e['Score'])) {
        $wid = $e['WorkID'];
        if (!isset($workScores[$wid])) $workScores[$wid] = ['sum' => 0, 'count' => 0];
        $workScores[$wid]['sum'] += $e['Score'];
        $workScores[$wid]['count'] += 1;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <title>評審評分與得獎整理</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body { background: #f4f4f9; padding: 30px; }
            h1 { margin-bottom: 20px; }
            table { background-color: white; }
            .nav-buttons { margin-bottom: 20px; text-align: center; }
            .nav-buttons .btn { margin: 5px; }
        </style>
    </head>

    <body>
        <div class="container">
            <h1 class="text-center">🏆 評審結果管理</h1>

            <!-- 評分總表 -->
            <div id="scoresView">
            <table id="scoreTable" class="table table-bordered table-hover">
                <thead class="thead-success">
                    <tr>
                        <th onclick="sortTable(0, 'scoreTable')">作品名稱</th>
                        <th onclick="sortTable(1, 'scoreTable')">評審姓名</th>
                        <th onclick="sortTable(2, 'scoreTable')">分數</th>
                        <th>評語</th>
                    </tr>
                </thead>
                <tbody id="scoreBody">
                <!-- 動態插入資料 -->
                </tbody>
            </table>
            </div>

            <!-- 平均分數 -->
            <div id="averageView" style="display: none;">
            <table id="avgTable" class="table table-bordered table-hover">
                <thead class="thead-success">
                    <tr>
                        <th onclick="sortTable(0, 'avgTable')">作品名稱</th>
                        <th onclick="sortTable(1, 'avgTable')">平均分數</th>
                        <th onclick="sortTable(2, 'avgTable')">參與評分人數</th>
                        <th>🏅 排名</th>
                    </tr>
                </thead>
                <tbody id="avgBody">
                <!-- 動態插入平均資料 -->
                </tbody>
            </table>
            </div>
        </div>

        <div class="nav-buttons">
            <button class="btn btn-primary" onclick="switchView('scores')">📋 評分總表</button>
            <button class="btn btn-info" onclick="switchView('average')">📊 平均分數</button>
            <a href="./final_publish.php" class="btn btn-secondary">📢 發布得獎公告</a>
            <a href="../admin_system.php" class="btn btn-secondary">⬅️ 返回管理首頁</a>
        </div>

        <script>

            const scores = <?= json_encode(array_map(function ($e) use ($workMap, $judgeMap) {
                return [
                    'work' => $workMap[$e['WorkID']] ?? '未知作品',
                    'judge' => $judgeMap[$e['JudgeID']] ?? '未知評審',
                    'score' => is_numeric($e['Score']) ? floatval($e['Score']) : null,
                    'comment' => $e['Comments'] ?? '尚未填寫'
                ];
            }, $evaluations), JSON_UNESCAPED_UNICODE); ?>;

            function renderTables() {
                const scoreBody = document.getElementById('scoreBody');
                const avgBody = document.getElementById('avgBody');
                const avgMap = {};

                scoreBody.innerHTML = '';
                avgBody.innerHTML = '';

                // 原始評分資料插入
                scores.forEach(({ work, judge, score, comment }) => {
                    scoreBody.innerHTML += `
                    <tr>
                        <td>${work}</td>
                        <td>${judge}</td>
                        <td>${score !== null ? score : '尚未評分'}</td>
                        <td>
                            ${comment && comment !== '尚未填寫'
                                ? `<button class='btn btn-success btn-sm' onclick="alert('評語：\\n\\n${comment}')">查看</button>`
                                : '<span>尚未填寫</span>'}
                        </td>
                    </tr>`;

                    // 收集有效分數用來計算平均
                    if (score !== null) {
                        if (!avgMap[work]) avgMap[work] = { total: 0, count: 0 };
                        avgMap[work].total += score;
                        avgMap[work].count++;
                    }
                });

                // 轉為陣列並依平均分數排序
                const avgList = Object.entries(avgMap).map(([work, data]) => ({
                    work,
                    avg: data.total / data.count,
                    count: data.count
                }));
                avgList.sort((a, b) => b.avg - a.avg);

                // 並列排名（不跳名次）
                let currentRank = 1;
                let rankCount = 0;
                let lastScore = null;

                avgList.forEach((item, index) => {
                    let rankLabel = '';

                    if (lastScore === null || item.avg !== lastScore) {
                        currentRank += rankCount; // 累加前面並列的數量
                        rankCount = 1;
                    } else {
                        rankCount++; // 這一個和上一個同分
                    }

                    // 根據 currentRank 指定獎牌
                    if (currentRank === 1) rankLabel = '🏆 金牌';
                    else if (currentRank === 2) rankLabel = '🥈 銀牌';
                    else if (currentRank === 3) rankLabel = '🥉 銅牌';

                    avgBody.innerHTML += `
                        <tr>
                            <td>${item.work}</td>
                            <td>${item.avg.toFixed(2)}</td>
                            <td>${item.count}</td>
                            <td>${rankLabel}</td>
                        </tr>`;

                    lastScore = item.avg;
                });
            }

            function switchView(view) {
                document.getElementById('scoresView').style.display = view === 'scores' ? 'block' : 'none';
                document.getElementById('averageView').style.display = view === 'average' ? 'block' : 'none';
            }

            function sortTable(col, tableId) {
                const table = document.getElementById(tableId);
                const rows = Array.from(table.rows).slice(1);
                const isNum = !isNaN(rows[0].cells[col].textContent);
                const sorted = rows.sort((a, b) => {
                    const x = a.cells[col].textContent.trim();
                    const y = b.cells[col].textContent.trim();
                    return isNum ? parseFloat(x) - parseFloat(y) : x.localeCompare(y);
                });
                sorted.forEach(row => table.tBodies[0].appendChild(row));
            }

            renderTables();
        </script>

    </body>

</html>

