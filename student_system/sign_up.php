<?php
// sign_up.php
session_start();
require_once 'config.php';  // 定義 supabaseRequest() 與 generateUUID()

// 回應 JSON
header('Content-Type: application/json; charset=utf-8');

// 只接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => '只接受 POST 請求',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. 先註冊每個參賽者 (Participants)
$membersEmails  = $_POST['emails'] ?? [];  // array of email
$participantIDs = [];

foreach ($membersEmails as $email) {
    $pid = generateUUID();
    $payload = [[
        'ParticipantID' => $pid,
        'Name'          => trim($_POST['name_' . $email]  ?? ''),
        'Phone'         => intval($_POST['phone_' . $email] ?? 0),
        'Email'         => $email,
        'Password'      => password_hash($_POST['pwd_' . $email] ?? '', PASSWORD_BCRYPT),
        'Role'          => 'Student',
    ]];

    $resP = supabaseRequest('Participants', 'POST', $payload);
    if ($resP['status'] !== 201) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error'   => '參賽者註冊失敗',
            'detail'  => $resP['body'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $participantIDs[] = $pid;
}

// 2. 建立隊伍 (All-Teams) — 注意表名需 URL-encode 雙引號
$teamId        = generateUUID();
$competitionId = $_POST['competition_id'] ?? '';

$teamsEndpoint = '%22All-Teams%22'; // 等同於 "All-Teams"
$payloadTeam   = [[
    'TeamID'        => $teamId,
    'TeamName'      => trim($_POST['teamName']     ?? ''),
    'CompetitionId' => $competitionId,
    // WorkID 留空，上傳作品時再更新
]];

$resT = supabaseRequest($teamsEndpoint, 'POST', $payloadTeam);
if ($resT['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => '隊伍建立失敗',
        'status'  => $resT['status'],
        'detail'  => $resT['body'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. 批次寫入隊員對應 (Team-Members)
$tmEndpoint = '%22Team-Members%22'; // 等同於 "Team-Members"
$tmPayload  = [];

foreach ($participantIDs as $pid) {
    $tmPayload[] = [
        'TeamID'        => $teamId,
        'ParticipantID' => $pid,
    ];
}

$resTM = supabaseRequest($tmEndpoint, 'POST', $tmPayload);
if ($resTM['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Team-Members 寫入失敗',
        'status'  => $resTM['status'],
        'detail'  => $resTM['body'],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. 註冊成功，存 Session 並回傳
$_SESSION['team_id'] = $teamId;

echo json_encode([
    'success' => true,
    'team_id' => $teamId,
], JSON_UNESCAPED_UNICODE);
