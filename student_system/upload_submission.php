<?php
// upload_submission.php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';          // 你自己的 callSupabase() 函式

// ╭──────────────────────────────────────────────────╮
// │ 1) 讀取 POST 參數（全部用資料表同名）             │
// ╰──────────────────────────────────────────────────╯
$team_id     = trim($_POST['team_id']          ?? '');
$description = trim($_POST['work_description'] ?? '');
$poster_url  = trim($_POST['poster_url']       ?? '');
$video_url   = trim($_POST['video_url']        ?? '');
$code_url    = trim($_POST['code_url']         ?? '');

// (1) team_id 必填
if ($team_id === '') {
    echo json_encode([
        'success' => false,
        'error'   => '缺少 team_id，請重新登入'
    ]);
    exit;
}
// (2) description 亦必填
if ($description === '') {
    echo json_encode([
        'success' => false,
        'error'   => '作品說明不能空白'
    ]);
    exit;
}

// ╭──────────────────────────────────────────────────╮
// │ 2) 組裝要寫入 Supabase 的資料                    │
// ╰──────────────────────────────────────────────────╯
$insert_data = [
    'team_id'     => $team_id,
    'description' => $description,
    // 轉成 NULL 可以避免空字串被誤當有效值
    'poster_url'  => $poster_url !== '' ? $poster_url : null,
    'video_url'   => $video_url  !== '' ? $video_url  : null,
    'code_url'    => $code_url   !== '' ? $code_url   : null
];

// ⚠ 如果你的 table 名叫 "Works"，路徑就是 "Works"
$r = callSupabase('Works', 'POST', $insert_data);

if ($r['status'] >= 200 && $r['status'] < 300) {
    echo json_encode(['success' => true]);
} else {
    // Supabase 會帶回錯誤訊息在 $r['body'] (JSON)
    $msg = $r['body']['message'] ?? '資料庫寫入失敗';
    echo json_encode([
        'success' => false,
        'error'   => $msg
    ]);
}
