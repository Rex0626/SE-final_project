<?php
// view_results.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// 取 team_id
$teamId = $_GET['team_id'] ?? $_SESSION['team_id'] ?? null;
if (!$teamId) {
  http_response_code(400);
  echo json_encode(['success'=>false,'error'=>'必須提供 team_id'], JSON_UNESCAPED_UNICODE);
  exit;
}

// 1) 找 WorkID
$rT = callSupabase("All-Teams?TeamID=eq.{$teamId}&select=WorkID", 'GET');
if ($rT['status']!==200 || empty($rT['body'][0]['WorkID'])) {
  http_response_code(404);
  echo json_encode(['success'=>false,'error'=>'找不到作品'], JSON_UNESCAPED_UNICODE);
  exit;
}
$workId = $rT['body'][0]['WorkID'];

// 2) 取作品資料
$rW = callSupabase(
  "Works?WorkID=eq.{$workId}&select=Description,Poster,VideoLink,CodeLink,created_at",
  'GET'
);
$work = $rW['body'][0] ?? [];

// 3) 取所有評分
$rE = callSupabase(
  "Evaluations?WorkID=eq.{$workId}&select=Score,Comments,created_at&order=created_at.asc",
  'GET'
);
$evals = is_array($rE['body']) ? $rE['body'] : [];

// 4) 取最新公告
$rN = callSupabase(
  "Announcement?select=Title,Content,Year&order=created_at.desc&limit=5",
  'GET'
);
$anns = is_array($rN['body']) ? $rN['body'] : [];

// 回傳
echo json_encode([
  'success'       => true,
  'work'          => $work,
  'evaluations'   => $evals,
  'announcements' => $anns
], JSON_UNESCAPED_UNICODE);
