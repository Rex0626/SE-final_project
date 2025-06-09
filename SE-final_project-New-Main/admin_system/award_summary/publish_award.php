<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// ✅ 共用抓資料函數
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
    return json_decode($res, true);
}

// ✅ 抓取比賽 ID 與比賽名稱
if (!isset($_GET['competition_id'])) {
    echo "<h3 style='color:red'>❌ 缺少 competition_id</h3>";
    exit();
}
$competition_id = $_GET['competition_id'];
$competition = fetch('Competitions', "Id=eq.$competition_id", 'Title');
$competitionTitle = $competition[0]['Title'] ?? '未知比賽';

// ✅ 若送出表單：發佈公告
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $year = $_POST['year'];
    $adminEmail = $_SESSION['email'];

    $data = [
        'Admin_Email' => $adminEmail,
        'Title' => $title,
        'Content' => $content,
        'Year' => $year
    ];

    $ch = curl_init($baseUrl . 'Announcement');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey
    ]);
    $res = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 201) {
        echo "<script>alert('✅ 公告已成功上傳,請到公告系統進行後續的發布'); window.location.href = './select_competition.php';</script>";
        exit();
    } else {
        echo "<h3 style='color:red'>❌ 上傳失敗（HTTP $httpcode）</h3>";
        echo "<pre>🔴 回傳內容：" . htmlspecialchars($res) . "</pre>";
    }
}

// ✅ 評分資料與隊伍資料
$teams = fetch('All-Teams', "CompetitionId=eq.$competition_id", 'TeamName,WorkID');
$works = fetch('Works', '', 'WorkID,Description');
$evaluations = fetch('Evaluations', '', 'WorkID,Score');

$workMap = [];
foreach ($works as $w) $workMap[$w['WorkID']] = $w['Description'] ?? '未知作品';

$scoreMap = [];
foreach ($evaluations as $e) {
    if (!is_numeric($e['Score'])) continue;
    $wid = $e['WorkID'];
    if (!isset($scoreMap[$wid])) $scoreMap[$wid] = ['sum' => 0, 'count' => 0];
    $scoreMap[$wid]['sum'] += $e['Score'];
    $scoreMap[$wid]['count']++;
}

$results = [];
foreach ($teams as $t) {
    $wid = $t['WorkID'];
    if (!isset($scoreMap[$wid])) continue;
    $avg = $scoreMap[$wid]['sum'] / $scoreMap[$wid]['count'];
    $results[] = [
        'team' => $t['TeamName'],
        'work' => $workMap[$wid] ?? '未知作品',
        'avg' => $avg
    ];
}

usort($results, fn($a, $b) => $b['avg'] <=> $a['avg']);

// ✅ 產生前三名（並列處理）
$ranked = [];
$currentRank = 0;
$lastAvg = null;
foreach ($results as $item) {
    if ($currentRank >= 3 && $item['avg'] !== $lastAvg) break;
    if ($item['avg'] !== $lastAvg) $currentRank++;
    $ranked[$currentRank][] = $item;
    $lastAvg = $item['avg'];
}

// ✅ 公告內容組成
$title = "{$competitionTitle} 得獎名單公告";
$year = date('Y') . "-01-01";
$content = "📢「{$competitionTitle}」得獎名單（" . date('Y') . " 年度）\n\n";

foreach ($ranked as $rank => $items) {
    $label = $rank === 1 ? "🥇 第一名：" : ($rank === 2 ? "🥈 第二名：" : "🥉 第三名：");
    $line = array_map(fn($i) => "{$i['team']} - {$i['work']}", $items);
    $content .= $label . implode("、", $line) . "\n";
}
$content .= "\n🎉 恭喜以上隊伍！完整成績請參考評分頁面。";
?>


<!DOCTYPE html>
<html lang="zh-Hant">

    <head>
        <meta charset="UTF-8">
        <title>發佈得獎公告</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            body { background: #f4f4f9; padding-top: 50px; }
            .container { max-width: 600px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        </style>
    </head>

    <style type="text/css">
        
        .btn{
            border: none;
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

        .btn-success{
            background-color: #69503F;
        }
        .btn-secondary{
            background-color: #FFEFD4;
            color: #221F1F;
        }

        .btn-primary{
                background-color: #69503F;
            }
            .btn-info{
                background-color: #B39870;
            }
            .btn-success{
                background-color: #E1C6A8;
            }
            .btn-secondary{
                background-color: #FFEFD4;
                color: #221F1F;
            }

    </style>

    <body  style="background-color: #FFF9F4;">
        <div class="container">
            <h2 class="text-center" style="color: #69503F;">發佈得獎公告</h2>
            <h5 class="text-center text-muted">比賽名稱：<?= htmlspecialchars($competitionTitle) ?></h5>
            <form method="POST">
                <div class="form-group">
                    <label>公告標題</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
                </div>
                <div class="form-group">
                    <label>公告內容</label>
                    <textarea name="content" class="form-control" rows="10" required><?= htmlspecialchars($content) ?></textarea>
                </div>
                <div class="form-group">
                    <label>公告年度</label>
                    <input type="date" name="year" class="form-control" value="<?= $year ?>" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success">發佈公告</button>
                    <a href="./select_competition.php" class="btn btn-secondary ml-2">返回首頁</a>
                </div>
            </form>
        </div>
    </body>

</html>