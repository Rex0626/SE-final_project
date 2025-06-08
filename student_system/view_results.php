<?php
session_start();
include 'config_rest.php';

$team_id = $_SESSION['team_id'] ?? exit('請先登入');

// GET Evaluations?TeamID=eq.{team_id}&select=total_score,ranking,comments_url
[$s,$body] = supabaseRequest("Evaluations?TeamID=eq.{$team_id}&select=total_score,ranking,comments_url",'GET');
if ($s!==200) exit("查詢失敗");

if (empty($body)) {
  echo "尚無成績";
} else {
  $r = $body[0];
  echo "總分：{$r['total_score']} 、 名次：{$r['ranking']} ";
  if(!empty($r['comments_url'])){
    echo "<a href='{$r['comments_url']}'>下載意見</a>";
  }
}
