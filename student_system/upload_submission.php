<?php
// upload_submission.php
session_start();
header('Content-Type: application/json; charset=utf-8');

// ── 基本驗證 ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'error'=>'必須使用 POST']);
    exit;
}
if (empty($_SESSION['StudentID'])) {
    echo json_encode(['success'=>false, 'error'=>'尚未登入 / 無效 Session']);
    exit;
}

// ── 讀取表單欄位 ────────────────────────────────────────
$studentId  = $_SESSION['StudentID'];
$desc       = trim($_POST['work_description'] ?? '');
$posterUrl  = trim($_POST['poster_url'] ?? '');
$videoUrl   = trim($_POST['video_url'] ?? '');
$codeUrl    = trim($_POST['code_url'] ?? '');

if ($desc === '') {
    echo json_encode(['success'=>false, 'error'=>'作品說明為必填']);
    exit;
}

// ── 寫入 Supabase (或資料庫) ─────────────────────────────
require_once 'config.php';     // 提供 callSupabase()
$payload = [[
    'StudentID'   => $studentId,
    'Description' => $desc,
    'PosterURL'   => $posterUrl ?: null,
    'VideoURL'    => $videoUrl  ?: null,
    'CodeURL'     => $codeUrl   ?: null,
]];

$r = callSupabase('Works', 'POST', $payload);

if ($r['status'] === 201) {
    echo json_encode(['success'=>true]);
} else {
    $msg = $r['body']['message'] ?? 'Supabase 寫入失敗';
    echo json_encode(['success'=>false, 'error'=>$msg]);
}
