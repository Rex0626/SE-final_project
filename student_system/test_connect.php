<?php
// test_connect.php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// 测试对 Participants 表做一次简单的 GET
$result = callSupabase('Participants?select=ParticipantID&limit=1', 'GET');
echo json_encode($result, JSON_UNESCAPED_UNICODE);