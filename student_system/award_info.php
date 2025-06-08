<?php
include 'config_rest.php';

// 1. 前 3 名
[$s1,$b1] = supabaseRequest('Evaluations?select=TeamID,total_score,ranking&order=ranking.asc&limit=3','GET');
if ($s1!==200) exit('查詢失敗');

// 2. 依序拿隊伍名稱、隊員、作品連結
foreach ($b1 as $row) {
  $tid = $row['TeamID'];
  // 2a. All-Teams?TeamID=eq.{tid}&select=TeamName
  [$s2,$b2] = supabaseRequest("All-Teams?TeamID=eq.{$tid}&select=TeamName",'GET');
  $tname = $b2[0]['TeamName'] ?? '—';

  // 2b. Team-Members?TeamID=eq.{tid}&select=ParticipantID
  [$s3,$b3] = supabaseRequest("Team-Members?TeamID=eq.{$tid}&select=ParticipantID",'GET');
  $ids = array_column($b3,'ParticipantID');
  // Participants?ParticipantID=in.({id1,id2})&select=Name,StudentNumber
  $plist = [];
  if ($ids) {
    $in = implode(',', array_map('urlencode',$ids));
    [$s4,$b4] = supabaseRequest("Participants?ParticipantID=in.({$in})&select=Name,StudentNumber",'GET');
    foreach ($b4 as $p) {
      $plist[] = "{$p['Name']}({$p['StudentNumber']})";
    }
  }
  $memberList = implode('<br>',$plist);

  // 2c. 作品連結
  [$s5,$b5] = supabaseRequest("All-Teams?TeamID=eq.{$tid}&select=WorkID",'GET');
  $wid = $b5[0]['WorkID'] ?? null;
  $links = [];
  if ($wid) {
    [$s6,$b6] = supabaseRequest("Works?WorkID=eq.{$wid}&select=Poster,VideoLink,CodeLink",'GET');
    $w = $b6[0];
    foreach (['Poster'=>'海報','VideoLink'=>'影片','CodeLink'=>'程式碼'] as $k=>$label) {
      if (!empty($w[$k])) {
        $links[] = "<a href='{$w[$k]}' target='_blank'>{$label}</a>";
      }
    }
  }
  $workLinks = implode(' | ',$links);

  echo "<h3>第 {$row['ranking']} 名 — {$tname}</h3>";
  echo "<p>總分：{$row['total_score']}</p>";
  echo "<p>隊員：{$memberList}</p>";
  echo "<p>作品：{$workLinks}</p>";
}
