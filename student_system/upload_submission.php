<?php
session_start();
header('Content-Type: application/json');
include 'config.php';

// 1. 一定要 POST 且已登入
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset($_SESSION['team_id'])) {
  echo json_encode([
    'success' => false,
    'error'   => '必須 POST 且已註冊'
  ]);
  exit;
}

$team_id   = $_SESSION['team_id'];
$workDesc  = trim($_POST['work_description'] ?? '');
$posterUrl = trim($_POST['poster_url']      ?? '');
$videoUrl  = trim($_POST['video_url']       ?? '');
$codeUrl   = trim($_POST['code_url']        ?? '');
$timestamp = date('c');

// 2. 拿 WorkID
list($s1, $b1) = supabaseRequest(
  "All-Teams?TeamID=eq.{$team_id}&select=WorkID",
  'GET'
);
if ($s1 !== 200 || empty($b1[0]['WorkID'])) {
  echo json_encode([
    'success' => false,
    'error'   => '找不到作品識別碼'
  ]);
  exit;
}
$work_id = $b1[0]['WorkID'];

// 3. 組更新欄位
$update = [ 'updated_at' => $timestamp ];
if ($workDesc  !== '') $update['Description'] = $workDesc;
if ($posterUrl !== '') $update['Poster']      = $posterUrl;
if ($videoUrl  !== '') $update['VideoLink']  = $videoUrl;
if ($codeUrl   !== '') $update['CodeLink']   = $codeUrl;

// 4. PATCH Works
list($s2, $b2) = supabaseRequest(
  "Works?WorkID=eq.{$work_id}",
  'PATCH',
  $update
);
if ($s2 !== 200) {
  echo json_encode([
    'success' => false,
    'error'   => '更新作品失敗：'. json_encode($b2)
  ]);
  exit;
}

// 5. 完成
echo json_encode(['success'=>true]);
