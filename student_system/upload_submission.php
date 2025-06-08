<?php
// upload_submission.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';   // 已停用 OpenSSL 驗證、定義 callSupabase()、generateUUID()
header('Content-Type: application/json; charset=utf-8');

// 驗證登入
if (!isset($_SESSION['team_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'請先註冊隊伍並登入'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 僅接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'只接受 POST 請求'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 讀取欄位
$teamId      = $_SESSION['team_id'];
$description = trim($_POST['work_description'] ?? '');
$poster      = trim($_POST['poster_url'] ?? null);
$video       = trim($_POST['video_url'] ?? null);
$code        = trim($_POST['code_url'] ?? null);

// 必填驗證
if (!$description) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'作品說明為必填'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 1) 插入 Works
$workId   = generateUUID();
$time     = date('c');
$workBody = [[
    'WorkID'      => $workId,
    'Description' => $description,
    'Poster'      => $poster,
    'VideoLink'   => $video,
    'CodeLink'    => $code,
    'created_at'  => $time,
    'updated_at'  => $time
]];
$res1 = callSupabase('Works', 'POST', $workBody);
if ($res1['status'] !== 201) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'寫入作品失敗'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2) 更新 All-Teams 的 WorkID
$teamUpd = [[ 'WorkID' => $workId ]];
$res2    = callSupabase("All-Teams?TeamID=eq.{$teamId}", 'PATCH', $teamUpd);
if ($res2['status'] !== 204) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'更新隊伍作品連結失敗'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 全部成功
echo json_encode(['success'=>true,'work_id'=>$workId], JSON_UNESCAPED_UNICODE);
