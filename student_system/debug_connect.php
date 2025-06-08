<?php
// debug_connect.php
require_once 'config.php';   // 确保这里能 include 到你填好 URL/KEY 的 config.php

header('Content-Type: application/json; charset=utf-8');

// 构造 cURL
$url = "{$SUPABASE_URL}/rest/v1/Participants?select=ParticipantID&limit=1";
$ch  = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$SUPABASE_KEY}",
    "Authorization: Bearer {$SUPABASE_KEY}",
    "Content-Type: application/json"
]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

$resp  = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 输出调试信息
echo json_encode([
    'url'       => $url,
    'errno'     => $errno,
    'error'     => $error,
    'http_code' => $code,
    'response'  => $resp,
], JSON_UNESCAPED_UNICODE);
