<?php
// list_submissions.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';   // 已定義 callSupabase() 等工具
header('Content-Type: application/json; charset=utf-8');

// 1) 取所有隊伍 (All-Teams)，包含 TeamID, TeamName, WorkID
$rTeams = callSupabase(
    'All-Teams?select=TeamID,TeamName,WorkID',
    'GET'
);
if ($rTeams['status'] !== 200) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => '無法讀取隊伍列表'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$teams = $rTeams['body'];

// 2) 收集所有有 WorkID 的隊伍
$workIds = [];
foreach ($teams as $t) {
    if (!empty($t['WorkID'])) {
        $workIds[] = $t['WorkID'];
    }
}
$workIds = array_unique($workIds);

$works = [];
if (!empty($workIds)) {
    // supabase REST API 使用 "in.(...)" 格式查詢
    $inList = implode(',', $workIds);
    $rWorks = callSupabase(
        "Works?select=WorkID,Description,Poster,VideoLink,CodeLink&WorkID=in.({$inList})",
        'GET'
    );
    if ($rWorks['status'] === 200) {
        // 以 WorkID 為 key 方便快速查
        foreach ($rWorks['body'] as $w) {
            $works[$w['WorkID']] = $w;
        }
    }
}

// 3) 將隊伍與作品對應
$result = [];
foreach ($teams as $t) {
    $result[] = [
        'TeamID'      => $t['TeamID'],
        'TeamName'    => $t['TeamName'],
        'work'        => isset($works[$t['WorkID']])
                         ? $works[$t['WorkID']]
                         : null
    ];
}

// 4) 回傳 JSON
echo json_encode([
    'success'     => true,
    'submissions' => $result
], JSON_UNESCAPED_UNICODE);
