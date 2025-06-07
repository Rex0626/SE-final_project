<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html?role=Teacher');
    exit();
}

$teacherEmail = $_SESSION['email'];

// API 呼叫工具函式
function callAPI($url, $method = 'GET', $data = null, $apiKey) {
    $ch = curl_init($url);
    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json",
        "Content-Type: application/json"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'PATCH' && $data) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// 資料表查詢工具函式
function fetch($table, $filter = '', $select = '*') {
    global $baseUrl, $apiKey;
    $url = $baseUrl . '/' . $table . '?select=' . urlencode($select);  // ← 這裡手動補上 `/`

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
        echo "<h3 style='color:red'>Supabase 回傳錯誤</h3>";
        echo "<p><strong>查詢網址：</strong><br>$url</p>";
        echo "<pre>" . htmlspecialchars($res) . "</pre>";
        exit();
    }
    return $decoded;
}

// 查教師 ParticipantID
$teacherData = callAPI("$baseUrl/Participants?select=*&Email=eq.$teacherEmail", 'GET', null, $apiKey);
if (empty($teacherData)) {
    echo "<p>找不到該老師的資料，請確認 Email 是否正確。</p>";
    exit();
}
$teacherID = $teacherData[0]['ParticipantID'];

// 查教師指導的 TeamIDs
$teamMembers = fetch('Team-Members', "ParticipantID=eq.$teacherID", 'TeamID');
if (empty($teamMembers)) {
    echo "這位老師沒有指導任何隊伍。";
    exit();
}
// 修正 TeamID 取值與清理
$teamIds = array_unique(array_map('trim', array_column($teamMembers, 'TeamID')));
$quotedTeamIds = array_map(fn($id) => "\"$id\"", $teamIds);
$teamIdStr = implode(',', $quotedTeamIds);
$teams = fetch('All-Teams', "TeamID=in.($teamIdStr)", 'TeamID, CompetitionId');

// 修正 CompetitionId 處理
$competitionIds = array_unique(array_map('trim', array_column($teams, 'CompetitionId')));
$competitionIdStr = implode(',', array_map(fn($id) => "\"$id\"", $competitionIds));
$competitions = fetch('Competitions', "Id=in.($competitionIdStr)", 'Id, Title');
$competitionTitle = $competitions[0]['Title'] ?? '未知比賽';


// 如果老師沒有參與任何比賽
if (empty($competitionIds)) {
    echo "<p>沒有找到您指導隊伍所參與的比賽。</p>";
    exit();
}

// 直接使用第一個比賽 ID（或你可以指定某一個）
$competition_id = $competitionIds[0];


// 資料查詢：作品、評分、評審
$teamWorks = fetch('All-Teams', "CompetitionId=eq.$competition_id", 'TeamName,WorkID');
$works = fetch('Works', '', 'WorkID,Description');
$evaluations = fetch('Evaluations', '', 'WorkID,JudgeID,Score,Comments');
$judges = fetch('Participants', 'Role=eq.Judge', 'ParticipantID,Name');

// 建立對照表
$workMap = [];
foreach ($works as $w) $workMap[$w['WorkID']] = $w['Description'];

$teamMap = [];
foreach ($teamWorks as $tw) $teamMap[$tw['WorkID']] = $tw['TeamName'];

$judgeMap = [];
foreach ($judges as $j) $judgeMap[$j['ParticipantID']] = $j['Name'];

// 過濾出該比賽作品的評分紀錄
$validWorkIds = array_keys($teamMap);
$evaluations = array_filter($evaluations, fn($e) => in_array($e['WorkID'], $validWorkIds));

// 後續你可以在這裡顯示 HTML 表格列出評分結果

// 計算每個作品的平均分數
$scoreMap = [];  // WorkID => [sum, count]
foreach ($evaluations as $e) {
    $wid = $e['WorkID'];
    if (!isset($scoreMap[$wid])) {
        $scoreMap[$wid] = ['sum' => 0, 'count' => 0];
    }
    $scoreMap[$wid]['sum'] += $e['Score'];
    $scoreMap[$wid]['count'] += 1;
}

$ranking = [];
foreach ($scoreMap as $wid => $data) {
    $avg = $data['count'] > 0 ? $data['sum'] / $data['count'] : 0;
    $ranking[] = [
        'TeamName' => $teamMap[$wid] ?? '未知隊伍',
        'Description' => $workMap[$wid] ?? '',
        'AverageScore' => round($avg, 2),
    ];
}

// 排名由高到低排序
usort($ranking, fn($a, $b) => $b['AverageScore'] <=> $a['AverageScore']);
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <title>比賽排名</title>
    <link rel="stylesheet" href="../styles.css" />
</head>
<body>
<header>
    <h1>高雄大學激發學生創意競賽指導教師系統</h1>
</header>

<nav>
    <ul class="drop-down-menu">
        <li><a href="../view_my_data/view_data.php">瀏覽隊伍資料</a></li>
        <li><a href="view_rank.php">瀏覽競賽排名</a></li>
        <li><a href="../modify_my_data/modify_data.php">修改個人資料</a></li>
    </ul>
</nav>

<a href="../../logout.php" class="logout-button">登出</a>

<div class="main-content">
    <div class="competition-title">
        <h2>目前比賽：<?= htmlspecialchars($competitionTitle) ?></h2>
    </div>
    <div class="card ranking-card">
        <div class="card-body">
            <ol class="ranking-list">
    <?php foreach ($ranking as $i => $row): ?>
        <li class="ranking-item">
            <h3>第 <?= $i + 1 ?> 名 - <?= htmlspecialchars($row['TeamName']) ?></h3>
            <p><strong>作品簡述：</strong><?= nl2br(htmlspecialchars($row['Description'])) ?></p>
        </li>
    <?php endforeach; ?>
</ol>

        </div>
    </div>
</div>


<footer>
    <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽指導教師系統</p>
</footer>
</body>
</html>




