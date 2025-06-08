<?php
// my_teams.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// **改用 team_id** （报名后存入的）：
if (! isset($_SESSION['team_id'])) {
    http_response_code(401);
    echo json_encode(array(
        'success' => false,
        'error'   => '請先註冊隊伍'
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$teamId = $_SESSION['team_id'];

// 直接从 All-Teams 拿这支队伍的名称
$r = callSupabase(
    "All-Teams?TeamID=eq.{$teamId}&select=TeamID,TeamName",
    'GET'
);
if ($r['status'] !== 200) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error'   => '無法讀取隊伍'
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array(
    'success' => true,
    // 返回一个数组，前端下拉列表能遍历
    'teams'   => $r['body']
), JSON_UNESCAPED_UNICODE);
