<?php
// sign_up.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';  // callSupabase(), generateUUID(), SSL 验证已禁用
header('Content-Type: application/json; charset=utf-8');

// 僅接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'只接受 POST 請求'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 讀取前端欄位
$emails        = $_POST['emails']            ?? [];
$teamName      = trim($_POST['teamName']     ?? '');
$competitionId = trim($_POST['competition_id'] ?? '');

// 驗證必填
if (empty($emails) || !$teamName || !$competitionId) {
    http_response_code(400);
    echo json_encode([
        'success'=>false,
        'error'=>'請提供 emails[], teamName, competition_id'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1) 處理 Participants：若已存在就拿 ID，否則 INSERT
$participantIDs = [];
foreach ($emails as $email) {
    $emailEsc = urlencode($email);
    // 查詢是否已存在
    $resGet = callSupabase("Participants?Email=eq.{$emailEsc}&select=ParticipantID", 'GET');
    if ($resGet['status'] === 200 && !empty($resGet['body'][0]['ParticipantID'])) {
        $pid = $resGet['body'][0]['ParticipantID'];
    } else {
        // 不存在就新增
        $pid = generateUUID();
        $pl = [[
            'ParticipantID' => $pid,
            'Name'          => trim($_POST["name_{$email}"]  ?? ''),
            'Phone'         => intval($_POST["phone_{$email}"] ?? 0),
            'Email'         => $email,
            'Password'      => password_hash($_POST["pwd_{$email}"] ?? '', PASSWORD_BCRYPT),
            'Role'          => 'Student',
            'created_at'    => date('c')
        ]];
        $resIns = callSupabase('Participants', 'POST', $pl);
        if ($resIns['status'] !== 201) {
            http_response_code(500);
            echo json_encode([
                'success'=>false,
                'step'=>'insert participants',
                'status'=>$resIns['status'],
                'detail'=>$resIns['body']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    $participantIDs[] = $pid;
}

// 2) 建立 All-Teams
$teamId        = generateUUID();
$teamsEndpoint = 'All-Teams';  // ← 直接用表名，不需引号或 %22
$teamPayload   = [[
    'TeamID'        => $teamId,
    'TeamName'      => $teamName,
    'CompetitionId' => $competitionId,
    'created_at'    => date('c')
]];
$resTeam = callSupabase($teamsEndpoint, 'POST', $teamPayload);
if ($resTeam['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'step'=>'insert team',
        'status'=>$resTeam['status'],
        'detail'=>$resTeam['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3) 批次寫入 Team-Members (移除 created_at)
$tmEndpoint = 'Team-Members';
$tmPayload  = [];
foreach ($participantIDs as $pid) {
    $tmPayload[] = [
        'TeamID'        => $teamId,
        'ParticipantID' => $pid
    ];
}
$resTM = callSupabase($tmEndpoint, 'POST', $tmPayload);
if ($resTM['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step'    => 'insert team-members',
        'status'  => $resTM['status'],
        'detail'  => $resTM['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4) 註冊成功，存 Session 並回傳
$_SESSION['team_id'] = $teamId;
echo json_encode([
    'success' => true,
    'team_id' => $teamId
], JSON_UNESCAPED_UNICODE);
