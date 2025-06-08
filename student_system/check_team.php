<?php
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role
$headers = [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
    "Content-Type: application/json",
    "Accept: application/json"
];

// 取得目前登入者的 Email（假設已登入並存於 session）
session_start();
$email = $_SESSION['email'] ?? null;
if (!$email) {
    echo json_encode(['status' => 'error', 'message' => '尚未登入']);
    exit;
}

function curl_supa_request($endpoint, $method = 'GET', $data = null) {
    global $SUPABASE_URL, $headers;

    $url = $SUPABASE_URL . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    return json_decode($response, true);
}

// 查詢 Participant 是否有在隊伍中
$response = curl_supa_request("Participants?Email=eq.$email", 'GET');
if (!$response || count($response) == 0) {
    echo json_encode(['status' => 'error', 'message' => '找不到使用者']);
    exit;
}
$participantID = $response[0]['ParticipantID'];

// 查詢是否有參加過隊伍
$response = curl_supa_request("Team-Members?ParticipantID=eq.$participantID", 'GET');
if (!$response || count($response) == 0) {
    echo json_encode(['status' => 'error', 'message' => '您尚未加入任何隊伍']);
    exit;
}
$teamID = $response[0]['TeamID'];

// 取回隊伍與作品資訊
$teamData = curl_supa_request("All-Teams?TeamID=eq.$teamID", 'GET');
$members = curl_supa_request("Team-Members?TeamID=eq.$teamID", 'GET');

if (!$teamData || !$members) {
    echo json_encode(['status' => 'error', 'message' => '查詢隊伍資訊失敗']);
    exit;
}

// 查詢所有成員 Email
$emails = [];
foreach ($members as $m) {
    $p = curl_supa_request("Participants?ParticipantID=eq." . $m['ParticipantID'], 'GET');
    if ($p && count($p) > 0) {
        $emails[] = $p[0]['Email'];
    }
}

echo json_encode([
    'status' => 'success',
    'data' => [
        'teamName' => $teamData[0]['TeamName'],
        'workName' => $teamData[0]['WorkID'], // 可再查 `Works` 表取得名稱
        'members' => $emails
    ]
]);