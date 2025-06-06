<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'config.php'; // Supabase 連線

// 先取得這個使用者所屬的 team_id
$teamId = $_SESSION['team_id'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>查詢隊伍成績</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h2>我的隊伍成績</h2>
  <?php
  // 查詢 results
  $response = $supabase
    ->from('results')
    ->select('total_score, ranking, comments_url, team_id')
    ->eq('team_id', $teamId)
    ->execute();

  if ($response->getStatus() === 200) {
    $data = $response->getBody();
    if (count($data) > 0) {
      $res = $data[0];
      echo "<table class='table table-striped'>";
      echo "<thead><tr><th>隊伍名稱</th><th>總分</th><th>名次</th><th>評審意見</th></tr></thead><tbody>";

      // 再去撈隊伍名稱
      $teamRes = $supabase
        ->from('teams')
        ->select('team_name')
        ->eq('id', $teamId)
        ->single()
        ->execute();
      $teamName = ($teamRes->getStatus() === 200) 
        ? htmlspecialchars($teamRes->getBody()->team_name) 
        : '未知隊伍';

      $commentsLink = $res->comments_url 
        ? "<a href='" . htmlspecialchars($res->comments_url) . "' target='_blank'>下載意見</a>" 
        : '無';

      echo "<tr>
              <td>{$teamName}</td>
              <td>{$res->total_score}</td>
              <td>{$res->ranking}</td>
              <td>{$commentsLink}</td>
            </tr>";
      echo "</tbody></table>";
    } else {
      echo "<div class='alert alert-info'>目前尚未有成績，請稍後再查詢。</div>";
    }
  } else {
    echo "<div class='alert alert-danger'>查詢失敗，狀態碼：" . $response->getStatus() . "</div>";
  }
  ?>
</div>
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
