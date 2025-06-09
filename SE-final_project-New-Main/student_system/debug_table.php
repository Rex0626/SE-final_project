<?php
// debug_table.php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// 嘗試不同的 table endpoint
$tests = [
  // 不帶任何引用
  'All-Teams?select=TeamID&limit=1',
  // 帶 literal quotes，假設 curl 會自動 encode
  '"All-Teams"?select=TeamID&limit=1',
  // 帶 rawurlencode 後的 %22…%22
  rawurlencode('"All-Teams"') . '?select=TeamID&limit=1',
];

$out = [];
foreach ($tests as $endpoint) {
  $r = callSupabase($endpoint, 'GET');
  $out[$endpoint] = [
    'status' => $r['status'],
    'body'   => $r['body']
  ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
