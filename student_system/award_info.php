<?php
require_once 'config.php';

$resA = $supabase
  ->from('Announcement')
  ->select('*')
  ->order('created_at', 'desc')
  ->execute();

if ($resA->getStatus() !== 200) {
  exit('讀取公告失敗：'. json_encode($resA->getBody()));
}

foreach ($resA->getBody() as $ann) {
  echo "<h2>{$ann['Title']} ({$ann['Year']})</h2>";
  echo "<p>{$ann['Content']}</p>";
  echo "<small>發布者：{$ann['Admin_Email']} ・ ". date('Y-m-d H:i', strtotime($ann['created_at'])) ."</small><hr>";
}
