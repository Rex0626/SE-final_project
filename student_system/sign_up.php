<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$res = callSupabase('Participants?select=ParticipantID&limit=1', 'GET');
echo json_encode([
  'status' => $res['status'],
  'body'   => $res['body']
], JSON_UNESCAPED_UNICODE);
