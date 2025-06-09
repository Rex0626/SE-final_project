<?php
// 設定 Supabase API 資訊
$SUPABASE_URL = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // 建議使用 Server Role
$headers = [
    "apikey: $SUPABASE_KEY",
    "Authorization: Bearer $SUPABASE_KEY",
    "Content-Type: application/json"
];

// 假設已透過 session 或其他方式取得登入者 email
session_start();
$loginEmail = $_SESSION['email'] ?? null;

if (!$loginEmail) {
    http_response_code(401);
    exit("請先登入");
}

// 取得表單資料
$teamName = $_POST['teamName'] ?? '';
$workName = $_POST['workName'] ?? '';
$emails = $_POST['emails'] ?? [];

if (count($emails) < 2 || count($emails) > 6) {
    http_response_code(400);
    exit("成員數量必須在 2 到 6 人之間");
}

// 第一步：查詢目前登入者的 TeamID、WorkID
function fetchTeamInfo($email, $SUPABASE_URL, $headers) {
    $url = $SUPABASE_URL . "/rest/v1/Participants?Email=eq." . urlencode($email) . "&select=ParticipantID";
    $res = curl_get($url, $headers);
    $participantID = $res[0]['ParticipantID'] ?? null;

    if (!$participantID) return [null, null, null];

    // 查 TeamID
    $url = $SUPABASE_URL . "/rest/v1/Team-Members?ParticipantID=eq.$participantID&select=TeamID";
    $res = curl_get($url, $headers);
    $teamID = $res[0]['TeamID'] ?? null;

    if (!$teamID) return [null, null, null];

    // 查 WorkID
    $url = $SUPABASE_URL . "/rest/v1/All-Teams?TeamID=eq.$teamID&select=WorkID";
    $res = curl_get($url, $headers);
    $workID = $res[0]['WorkID'] ?? null;

    return [$participantID, $teamID, $workID];
}

// 呼叫 GET API 的輔助函數
function curl_get($url, $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 呼叫 PATCH/POST/DELETE 的輔助函數
function curl_request($url, $headers, $method, $data = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 取得隊伍資訊
list($myID, $teamID, $workID) = fetchTeamInfo($loginEmail, $SUPABASE_URL, $headers);
if (!$teamID || !$workID) {
    http_response_code(404);
    exit("找不到使用者所屬的隊伍");
}

// 更新隊名與作品名稱
curl_request("$SUPABASE_URL/rest/v1/All-Teams?TeamID=eq.$teamID", $headers, "PATCH", [ "TeamName" => $teamName ]);
curl_request("$SUPABASE_URL/rest/v1/Works?WorkID=eq.$workID", $headers, "PATCH", [ "Description" => $workName ]);

// 清除舊 Team-Members
curl_request("$SUPABASE_URL/rest/v1/Team-Members?TeamID=eq.$teamID", $headers, "DELETE");

// 將成員 email 轉為 ParticipantID
$newMembers = [];
foreach ($emails as $email) {
    $res = curl_get("$SUPABASE_URL/rest/v1/Participants?Email=eq." . urlencode($email) . "&select=ParticipantID", $headers);
    $pid = $res[0]['ParticipantID'] ?? null;
    if ($pid) {
        $newMembers[] = [
            "TeamID" => $teamID,
            "ParticipantID" => $pid,
        ];
    }
}

if (count($newMembers) === 0) {
    http_response_code(400);
    exit("無有效成員");
}

// 寫入新 Team-Members
curl_request("$SUPABASE_URL/rest/v1/Team-Members", $headers, "POST", $newMembers);

header("Location: student_system.html");
?>
