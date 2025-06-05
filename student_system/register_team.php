<?php
date_default_timezone_set('Asia/Taipei');

// ✅ Supabase 設定
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role

// ✅ 取得前端送來的資料
$team_name = $_POST['teamName'];
$work_name = $_POST['workName'];
$emails = $_POST['emails']; // 這是一個 array
$year = $_POST['year'] ?? date('Y'); // 比賽年份

// ✅ 建立 UUID
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

$work_id = generateUUID();
$team_id = generateUUID();
$timestamp = date('c');

// ✅ Step 1. 插入 Works 表
$work_payload = json_encode([
    'WorkID' => $work_id,
    'Description' => $work_name,
    'created_at' => $timestamp,
    'updated_at' => $timestamp
]);

$ch = curl_init("{$SUPABASE_URL}/rest/v1/Works");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$SUPABASE_KEY}",
    "Authorization: Bearer {$SUPABASE_KEY}",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $work_payload);
curl_setopt($ch, CURLOPT_POST, true);
$response1 = curl_exec($ch);
curl_close($ch);

// ✅ Step 2. 插入 All-Teams 表
$team_payload = json_encode([
    'TeamID' => $team_id,
    'TeamName' => $team_name,
    'CompetitionYear' => $year . "-01-01",
    'created_at' => $timestamp,
    'WorkID' => $work_id
]);

$ch = curl_init("{$SUPABASE_URL}/rest/v1/All-Teams");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$SUPABASE_KEY}",
    "Authorization: Bearer {$SUPABASE_KEY}",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $team_payload);
curl_setopt($ch, CURLOPT_POST, true);
$response2 = curl_exec($ch);
curl_close($ch);

// ✅ Step 3. 查詢每位 Email 在 Participants 表中的 ParticipantID
$members = [];
foreach ($emails as $email) {
    $email_escaped = urlencode($email);
    $ch = curl_init("{$SUPABASE_URL}/rest/v1/Participants?Email=eq.{$email_escaped}&select=ParticipantID");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$SUPABASE_KEY}",
        "Authorization: Bearer {$SUPABASE_KEY}"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (!empty($data[0]['ParticipantID'])) {
        $members[] = [
            'TeamID' => $team_id,
            'ParticipantID' => $data[0]['ParticipantID'],
            'Year' => $year . "-01-01"
        ];
    }
}

// ✅ Step 4. 批次插入 Team-Members 表
$ch = curl_init("{$SUPABASE_URL}/rest/v1/Team-Members");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: {$SUPABASE_KEY}",
    "Authorization: Bearer {$SUPABASE_KEY}",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($members));
curl_setopt($ch, CURLOPT_POST, true);
$response4 = curl_exec($ch);
curl_close($ch);

// ✅ 回傳結果
echo json_encode([
    'success' => true,
    'team_id' => $team_id,
    'work_id' => $work_id,
    'members_inserted' => count($members),
]);
?>
