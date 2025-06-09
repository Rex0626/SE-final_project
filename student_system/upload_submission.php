<?php
// upload_submission.php  —— 2025-06-09 修正版
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';      // 內含 callSupabase()

// 1) 基本驗證
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'必須使用 POST']);
    exit;
}
$team_id = $_SESSION['team_id'] ?? null;
if (! $team_id) {
    echo json_encode(['success'=>false,'error'=>'尚未登入 / 無效 Session']);
    exit;
}

// 2) 讀取表單欄位
$desc       = trim($_POST['work_description'] ?? '');
$posterUrl  = trim($_POST['poster_url'] ?? '');
$videoUrl   = trim($_POST['video_url'] ?? '');
$codeUrl    = trim($_POST['code_url'] ?? '');
if ($desc === '') {
    echo json_encode(['success'=>false,'error'=>'作品說明為必填']);
    exit;
}

// 3) 透過 team_id 取得 WorkID
$r1 = callSupabase("All-Teams?TeamID=eq.{$team_id}&select=WorkID", 'GET');
if ($r1['status'] !== 200 || empty($r1['body'][0]['WorkID'])) {
    echo json_encode(['success'=>false,'error'=>'找不到作品識別碼']);
    exit;
}
$work_id = $r1['body'][0]['WorkID'];

// 4) 組 PATCH 欄位
$update = ['updated_at' => date('c')];
if ($desc)      $update['Description'] = $desc;
if ($posterUrl) $update['Poster']      = $posterUrl;
if ($videoUrl)  $update['VideoLink']   = $videoUrl;
if ($codeUrl)   $update['CodeLink']    = $codeUrl;

// 5) 更新 Works
$r2 = callSupabase("Works?WorkID=eq.{$work_id}", 'PATCH', $update);
if ($r2['status'] !== 200) {
    echo json_encode(['success'=>false,'error'=>'更新作品失敗：'.json_encode($r2['body'])]);
    exit;
}

// 6) 成功
echo json_encode(['success'=>true]);
