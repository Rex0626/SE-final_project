<?php
// sign_up.php
date_default_timezone_set('Asia/Taipei');
header('Content-Type: application/json; charset=utf-8');

// ← 在這裡填上你的專案 URL & Key ←
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role

/**
 * 執行 Supabase REST API 請求
 *
 * @param string      $endpoint  table 或 query (e.g. 'Participants', '%22All-Teams%22')
 * @param string      $method    HTTP 方法: GET, POST, PATCH, DELETE
 * @param array|null  $data      POST/PATCH 時的 payload
 * @return array                 ['status'=>HTTP code, 'body'=>decoded JSON]
 */
function callSupabase(string $endpoint, string $method = 'GET', array $data = null): array {
    global $SUPABASE_URL, $SUPABASE_KEY;
    $url = "{$SUPABASE_URL}/rest/v1/{$endpoint}";
    $ch = curl_init($url);
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
    return ['status'=>$code, 'body'=> json_decode($resp, true)];
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
    echo json_encode(['success'=>false,'error'=>'只接受 POST 請求'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 讀取前端資料
$teamName       = trim($_POST['teamName']       ?? '');
$competitionId  = trim($_POST['competition_id'] ?? '');
$emails         = $_POST['emails']             ?? [];

if (empty($teamName) || empty($competitionId) || !is_array($emails) || count($emails) === 0) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'請提供 teamName, competition_id 及至少一個 email'], JSON_UNESCAPED_UNICODE);
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
        'Role'          => 'Student',
        'created_at'    => date('c')
    ]];
    $res = callSupabase('Participants', 'POST', $pl);
    if ($res['status'] !== 201) {
        http_response_code(500);
        echo json_encode([
            'success'=>false,
            'step'   =>'insert participants',
            'status' =>$res['status'],
            'detail' =>$res['body']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $participantIDs[] = $pid;
}

// 2) 插入 All-Teams (表名含破折號需用 URL-encoded 的雙引號)
$teamId = generateUUID();
$teamsEndpoint = '%22All-Teams%22';
$teamPayload = [[
    'TeamID'        => $teamId,
    'TeamName'      => $teamName,
    'CompetitionId' => $competitionId,
    'created_at'    => date('c'),
    // 'WorkID'    => null  // 作品上傳時再更新
]];
$res = callSupabase($teamsEndpoint, 'POST', $teamPayload);
if ($res['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'step'   =>'insert team',
        'status' =>$res['status'],
        'detail' =>$res['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3) 批次插入 Team-Members
$tmEndpoint = '%22Team-Members%22';
$tmPayload = [];
foreach ($participantIDs as $pid) {
    $tmPayload[] = [
        'TeamID'        => $teamId,
        'ParticipantID' => $pid,
        'created_at'    => date('c'),
    ];
}
$res = callSupabase($tmEndpoint, 'POST', $tmPayload);
if ($res['status'] !== 201) {
    http_response_code(500);
    echo json_encode([
        'success'=>false,
        'step'   =>'insert team-members',
        'status' =>$res['status'],
        'detail' =>$res['body']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 成功
echo json_encode([
    'success'        => true,
    'team_id'        => $teamId,
    'participant_ids'=> $participantIDs
], JSON_UNESCAPED_UNICODE);
