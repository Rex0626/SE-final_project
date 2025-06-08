<?php
// sign_up.php
date_default_timezone_set('Asia/Taipei');
session_start();
header('Content-Type: application/json; charset=utf-8');

// ← 填上你的 Supabase 專案參數 ←
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role
/**
 * 發送 Supabase REST 請求
 */
function callSupabase(string $endpoint, string $method = 'GET', array $data = null): array {
    global $SUPABASE_URL, $SUPABASE_KEY;
    $url = "{$SUPABASE_URL}/rest/v1/{$endpoint}";
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$SUPABASE_KEY}",
        "Authorization: Bearer {$SUPABASE_KEY}",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $code, 'body' => json_decode($resp, true)];
}

/**
 * 產生 UUID v4
 */
function generateUUID(): string {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// 僅接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '只接受 POST 請求'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 讀取前端欄位
$emails        = $_POST['emails']          ?? [];
$teamName      = trim($_POST['teamName']   ?? '');
$competitionId = trim($_POST['competition_id'] ?? '');

// 驗證必填
if (empty($emails) || !$teamName || !$competitionId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => '請提供 emails[], teamName, competition_id'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1) 插入 Participants
$participantIDs = [];
foreach ($emails as $email) {
    $pid = generateUUID();
    $pl = [[
        'ParticipantID' => $pid,
        'Name'          => trim($_POST["name_{$email}"]  ?? ''),
        'Phone'         => intval($_POST["phone_{$email}"] ?? 0),
        'Email'         => $email,
        'Password'      => password_hash($_POST["pwd_{$email}"] ?? '', PASSWORD_BCRYPT),
        'Role'          => 'Student'
    ]];
    $res = callSupabase('Participants', 'POST', $pl);
    if ($res['status'] !== 201) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'step'    => 'insert participants',
            'status'  => $res['status'],
            'detail'  => $res['body']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $participantIDs[] = $pid;
}

// 2) 建立 All-Teams （表名含破折號要用 URL-encoded 雙引號）
$teamId       = generateUUID();
$teamsEndpoint = '%22All-Teams%22';
$teamPayload   = [[
    'TeamID'        => $teamId,
    'TeamName'      => $teamName,
    'CompetitionId' => $competitionId
]];
$res = callSupabase($teamsEndpoint, 'POST', $teamPayload);
if ($res['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step'    => 'insert team',
        'status'  => $res['status'],
        'detail'  => $res['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3) 批次插入 Team-Members
$tmEndpoint = '%22Team-Members%22';
$tmPayload  = [];
foreach ($participantIDs as $pid) {
    $tmPayload[] = [
        'TeamID'        => $teamId,
        'ParticipantID' => $pid
    ];
}
$res = callSupabase($tmEndpoint, 'POST', $tmPayload);
if ($res['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step'    => 'insert team-members',
        'status'  => $res['status'],
        'detail'  => $res['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4) 註冊成功，存 Session 並回傳 team_id
$_SESSION['team_id'] = $teamId;
echo json_encode([
    'success' => true,
    'team_id' => $teamId
], JSON_UNESCAPED_UNICODE);
