<?php
session_start();
include 'config_rest.php';

$team_name    = $_POST['teamName'];
$work_name    = $_POST['workName'];
$emails       = $_POST['emails'];       // array of emails
$year         = $_POST['year'] ?? date('Y');
$work_id      = generateUUID();
$team_id      = generateUUID();
$timestamp    = date('c');

// 1. 新增 Works
[$s1, $body1] = supabaseRequest('Works', 'POST', [
  'WorkID'      => $work_id,
  'Description' => $work_name,
  'created_at'  => $timestamp,
  'updated_at'  => $timestamp
]);
if ($s1 !== 201) exit("Works insert failed: ".json_encode($body1));

// 2. 新增 All-Teams
[$s2, $body2] = supabaseRequest('All-Teams', 'POST', [
  'TeamID'          => $team_id,
  'TeamName'        => $team_name,
  'CompetitionYear' => $year.'-01-01',
  'created_at'      => $timestamp,
  'WorkID'          => $work_id
]);
if ($s2 !== 201) exit("Teams insert failed: ".json_encode($body2));

// 3. 查出每個 Email 的 ParticipantID
$members = [];
foreach ($emails as $email) {
  $e = urlencode($email);
  // GET /Participants?Email=eq.xyz&select=ParticipantID
  [$s3,$body3] = supabaseRequest("Participants?Email=eq.{$e}&select=ParticipantID", 'GET');
  if ($s3===200 && !empty($body3[0]['ParticipantID'])) {
    $members[] = [
      'TeamID'        => $team_id,
      'ParticipantID' => $body3[0]['ParticipantID'],
      'Year'          => $year.'-01-01'
    ];
  }
}

// 4. 批次插入 Team-Members
[$s4,$body4] = supabaseRequest('Team-Members', 'POST', $members);
if ($s4 !== 201) exit("Team-Members insert failed: ".json_encode($body4));

// 回傳
echo json_encode([
  'success'          => true,
  'team_id'          => $team_id,
  'work_id'          => $work_id,
  'members_inserted' => count($members)
]);
?>
