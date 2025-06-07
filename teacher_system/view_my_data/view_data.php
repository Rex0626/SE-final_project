<script>
// 閒置時間上限：10 分鐘（單位：毫秒）
const timeoutDuration = 10 * 1000;

let timeoutHandle = setTimeout(showIdleWarning, timeoutDuration);

// 重設計時器函式
function resetTimer() {
    clearTimeout(timeoutHandle);
    timeoutHandle = setTimeout(showIdleWarning, timeoutDuration);
}

// 顯示提示視窗並導向登出
function showIdleWarning() {
    const userConfirmed = confirm("您已閒置超過 10 分鐘，系統即將登出。請按「確定」繼續。");
    if (userConfirmed) {
        window.location.href = "../../main.html?timeout=1";
    }
}

<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html?role=Teacher');
    exit();
}

$teacherEmail = $_SESSION['email'];

// 取得該老師的 ParticipantID
$teacherUrl = "$baseUrl/Participants?select=ParticipantID&Email=eq.$teacherEmail";
$teacherRes = file_get_contents($teacherUrl, false, stream_context_create([
    'http' => [
        'header' => [
            "apikey: $apiKey",
            "Authorization: Bearer $apiKey",
            "Accept: application/json"
        ]
    ]
]));
$teacherData = json_decode($teacherRes, true);

if (!is_array($teacherData) || count($teacherData) === 0) {
    echo "<p>找不到該老師的資料，請確認 Email 是否正確</p>";
    exit();
}
$teacherID = $teacherData[0]['ParticipantID'];

// 找出該老師的 TeamIDs
$teamMemberUrl = "$baseUrl/Team-Members?select=TeamID&ParticipantID=eq.$teacherID";
$teamMemberRes = file_get_contents($teamMemberUrl, false, stream_context_create([
    'http' => [
        'header' => [
            "apikey: $apiKey",
            "Authorization: Bearer $apiKey",
            "Accept: application/json"
        ]
    ]
]));
$teamIDs = json_decode($teamMemberRes, true);
$teams = [];
$students = [];
$works = [];

if (is_array($teamIDs)) {
    foreach ($teamIDs as $teamItem) {
        $teamID = $teamItem['TeamID'];

        // 隊伍資料
        $teamInfoUrl = "$baseUrl/All-Teams?select=*&TeamID=eq.$teamID";
        $teamInfoRes = file_get_contents($teamInfoUrl, false, stream_context_create([
            'http' => ['header' => ["apikey: $apiKey", "Authorization: Bearer $apiKey", "Accept: application/json"]]
        ]));
        $teamInfo = json_decode($teamInfoRes, true);
        if (!$teamInfo || !isset($teamInfo[0])) continue;
        $team = $teamInfo[0];
        $teams[] = $team;

        // 成員資料
        $memberUrl = "$baseUrl/Team-Members?select=ParticipantID&TeamID=eq.$teamID";
        $memberRes = file_get_contents($memberUrl, false, stream_context_create([
            'http' => ['header' => ["apikey: $apiKey", "Authorization: Bearer $apiKey", "Accept: application/json"]]
        ]));
        $members = json_decode($memberRes, true);
        $students[$teamID] = [];

        foreach ($members as $member) {
            $participantID = $member['ParticipantID'];
            $participantUrl = "$baseUrl/Participants?select=*&ParticipantID=eq.$participantID";
            $participantRes = file_get_contents($participantUrl, false, stream_context_create([
                'http' => ['header' => ["apikey: $apiKey", "Authorization: Bearer $apiKey", "Accept: application/json"]]
            ]));
            $participantInfo = json_decode($participantRes, true);
            if (isset($participantInfo[0])) {
                $students[$teamID][] = $participantInfo[0];
            }
        }

        // 作品資料
        if (!empty($team['WorkID'])) {
            $workUrl = "$baseUrl/Works?select=*&WorkID=eq.{$team['WorkID']}";
            $workRes = file_get_contents($workUrl, false, stream_context_create([
                'http' => ['header' => ["apikey: $apiKey", "Authorization: Bearer $apiKey", "Accept: application/json"]]
            ]));
            $workData = json_decode($workRes, true);
            $works[$teamID] = $workData;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>瀏覽隊伍資料</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
    	<h1>高雄大學激發學生創意競賽指導教師系統</h1>
    </header>
    <nav>
    <ul class="drop-down-menu">
        <li><a href="view_data.php">瀏覽隊伍資料</a></li>
        <li><a href="../view_rank/view_rank.php">瀏覽競賽排名</a></li>
        <li><a href="../modify_my_data/modify_data.php">修改個人資料</a></li>
    </ul>
    </nav>

    <a href="../../logout.php" class="logout-button">登出</a>


    <div class="main-content">
        <?php foreach ($teams as $team): ?>
            <div class="team-section">
                <h3>隊伍名稱：<?php echo htmlspecialchars($team['TeamName']); ?></h3>
                <h4>隊伍成員：</h4>
                <div class="student-cards">
                    <?php foreach ($students[$team['TeamID']] as $student): ?>
                        <?php if (strtolower($student['Role']) === 'student'): ?>
                            <div class="student-card">
                                <strong><?php echo htmlspecialchars($student['Name']); ?></strong>
                                <p>電話: <?php echo htmlspecialchars($student['Phone']); ?></p>
                                <p>Email: <?php echo htmlspecialchars($student['Email']); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <h4>作品檢視：</h4>
                <?php if (!empty($works[$team['TeamID']])): ?>
                    <?php foreach ($works[$team['TeamID']] as $work): ?>
                        <div class="work-item">
                            <p><strong>作品描述：</strong><br><?php echo nl2br(htmlspecialchars($work['Description'])); ?></p>
                            <div class="work-links">
                                <?php if (!empty($work['CodeLink'])): ?>
                                    <a href="<?php echo htmlspecialchars($work['CodeLink']); ?>" target="_blank">查看程式碼</a>
                                <?php endif; ?>
                                <?php if (!empty($work['VideoLink'])): ?>
                                    <a href="<?php echo htmlspecialchars($work['VideoLink']); ?>" target="_blank">觀看影片</a>
					</div>
                                <?php endif; ?>
                            <?php if (!empty($work['Poster'])): ?>
				<div class="poster-container">
                                <img src="<?php echo htmlspecialchars($work['Poster']); ?>" alt="作品海報">
				</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>尚無作品資料</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
