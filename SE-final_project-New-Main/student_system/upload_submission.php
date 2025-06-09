<?php
// upload_submission.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// 只接受已登入團隊的上傳
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['team_id'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'必須 POST 且已註冊'], JSON_UNESCAPED_UNICODE);
    exit;
}

$teamId = $_SESSION['team_id'];
$desc   = trim($_POST['work_description'] ?? '');
$poster = trim($_POST['poster_url']       ?? '');
$video  = trim($_POST['video_url']        ?? '');
$code   = trim($_POST['code_url']         ?? '');

if ($desc === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'請填作品說明'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1) 新增 Works
$workId = generateUUID();
$ts     = date('c');
$pl1    = [[
    'WorkID'      => $workId,
    'Description' => $desc,
    'created_at'  => $ts,
    'updated_at'  => $ts
]];
$r1 = callSupabase('Works','POST',$pl1);
if ($r1['status'] !== 201) {
    http_response_code(500);
    echo json_encode(['success'=>false,'step'=>'insert works','status'=>$r1['status']],JSON_UNESCAPED_UNICODE);
    exit;
}

// 2) 更新檔案連結
// 2) 更新檔案連結
$upd = [];
if ($poster!=='') $upd['Poster']    = $poster;
if ($video!=='')  $upd['VideoLink'] = $video;
if ($code!=='')   $upd['CodeLink']  = $code;
if ($upd) {
    $r2 = callSupabase("Works?WorkID=eq.{$workId}", 'PATCH', [$upd]);
    if (!in_array($r2['status'], [200, 204], true)) {
        http_response_code(500);
        echo json_encode([
          'success' => false,
          'step'    => 'update works links',
          'status'  => $r2['status'],
          'detail'  => $r2['body'] ?? null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 3) 回填 All-Teams
$r3 = callSupabase('All-Teams?TeamID=eq.'.$teamId, 'PATCH', [['WorkID'=>$workId]]);
// 接受 200 或 204 都算成功
if (! in_array($r3['status'], [200, 204], true)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'step'    => 'update team',
        'status'  => $r3['status'],
        'detail'  => $r3['body'] ?? null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['success'=>true,'work_id'=>$workId],JSON_UNESCAPED_UNICODE);
