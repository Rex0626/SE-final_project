<?php
// upload_submission.php   2025-06-09 不依賴 Session 版
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';     // callSupabase()

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'只能用 POST']);
    exit;
}

$team_id = $_POST['team_id'] ?? '';
if ($team_id === '') {
    echo json_encode(['success'=>false,'error'=>'缺少 team_id，請重新登入']);
    exit;
}

/* -------- 讀表單欄位 -------- */
$desc      = trim($_POST['work_description'] ?? '');
$posterUrl = trim($_POST['poster_url']       ?? '');
$videoUrl  = trim($_POST['video_url']        ?? '');
$codeUrl   = trim($_POST['code_url']         ?? '');

if ($desc === '') {
    echo json_encode(['success'=>false,'error'=>'作品說明必填']);
    exit;
}

/* -------- 透過 team_id 找 WorkID -------- */
$r = callSupabase("All-Teams?TeamID=eq.$team_id&select=WorkID", 'GET');
if ($r['status'] !== 200 || empty($r['body'][0]['WorkID'])) {
    echo json_encode(['success'=>false,'error'=>'查無 WorkID，請確認已建立隊伍']);
    exit;
}
$work_id = $r['body'][0]['WorkID'];

/* -------- 組 PATCH 欄位 -------- */
$payload = ['updated_at' => date('c'), 'Description' => $desc];
if ($posterUrl) $payload['Poster']    = $posterUrl;
if ($videoUrl)  $payload['VideoLink'] = $videoUrl;
if ($codeUrl)   $payload['CodeLink']  = $codeUrl;

/* -------- 更新 Works -------- */
$r2 = callSupabase("Works?WorkID=eq.$work_id", 'PATCH', $payload);
if ($r2['status'] !== 200) {
    echo json_encode(['success'=>false,'error'=>'更新失敗']);
    exit;
}

echo json_encode(['success'=>true]);
?>
