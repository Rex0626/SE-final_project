<?php
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role
$headers = [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
    "Content-Type: application/json",
    "Accept: application/json"
];

session_start();
$email = $_SESSION['email'] ?? null;

if (!$email) {
    // 未登入，導向登入頁
    header("Location: ../login.html");
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

// 1. 找 Participant

$participants = curl_supa_request("Participants?Email=eq.$email", 'GET');
if (!$participants || count($participants) == 0) {
    header("Location: ../register.html?msg=" . urlencode("找不到使用者"));
    exit;
}
$participantID = $participants[0]['ParticipantID'];

// 3. 檢查是否有尚未開始且可報名的比賽
$competitions = curl_supa_request("Competitions?StartDate=gt." . date('Y-m-d') . "&RegistrationOpen=eq.true", 'GET');
if (!$competitions || count($competitions) == 0) {
    header("Location: student_system.html?msg=" . urlencode("目前沒有可報名的比賽"));
    exit;
}

// 2. 檢查是否已報名
$registrations = curl_supa_request("Team-Members?ParticipantID=eq.$participantID", 'GET');
if ($registrations && count($registrations) > 0) {
    header("Location: student_system.html?msg=" . urlencode("您已報名過，不可重複報名"));
    exit;
}

// 都通過，直接導向報名頁
header("Location: registration_team.html");
exit;
