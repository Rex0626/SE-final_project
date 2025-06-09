<?php
// view_announcements.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php';   // defines callSupabase()
header('Content-Type: application/json; charset=utf-8');

// 1) 拉最新公告 (Announcement)，按 Year、created_at 倒序
$response = callSupabase(
    'Announcement?select=Title,Content,Year,created_at'
  . '&order=Year.desc,created_at.desc',
    'GET'
);

if ($response['status'] !== 200) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error'   => '無法取得公告'
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array(
    'success'       => true,
    'announcements' => $response['body']
), JSON_UNESCAPED_UNICODE);
