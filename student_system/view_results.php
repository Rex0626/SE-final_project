<?php
session_start();
if (!isset($_SESSION['team_id'])) {
  header('Location: login.php');
  exit;
}

require_once 'config.php';

$teamId = $_SESSION['team_id'];

// 1. 從 Evaluations 表找這支隊伍的成績
$resEval = $supabase
  ->from('Evaluations')
  ->select('total_score, ranking, comments_url')
  ->eq('TeamID', $teamId)
  ->single()  // 假設一支隊只有一筆最終評分
  ->execute();

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>我的隊伍成績</title>
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
  if ($resEval->getStatus() === 200) {
    $evalData = $resEval->getBody();
    // 如果沒有任何評分結果，$evalData 可能為 null
    if ($evalData) {
      // 顯示隊伍名稱（從 All-Teams 撈）
      $resTeam = $supabase
        ->from('All-Teams')
        ->select('TeamName')
        ->eq('TeamID', $teamId)
        ->single()
        ->execute();
      $teamName = ($resTeam->getStatus() === 200)
        ? htmlspecialchars($resTeam->getBody()->teamname)
        : '未知隊伍';

      echo "<table class='table table-striped'>";
      echo "<thead><tr><th>隊伍名稱</th><th>總分</th><th>名次</th><th>評審意見</th></tr></thead><tbody>";
      $score = $evalData->total_score;
      $rank  = $evalData->ranking;
      $commentsUrl = $evalData->comments_url
        ? "<a href='" . htmlspecialchars($evalData->comments_url) . "' target='_blank'>下載意見</a>"
        : '無';

      echo "<tr>
              <td>{$teamName}</td>
              <td>{$score}</td>
              <td>{$rank}</td>
              <td>{$commentsUrl}</td>
            </tr>";
      echo "</tbody></table>";

    } else {
      echo "<div class='alert alert-info'>目前尚未有評分結果，請稍後查看。</div>";
    }
  } else {
    echo "<div class='alert alert-danger'>查詢失敗，狀態碼：" . $resEval->getStatus() . "</div>";
  }
  ?>
</div>

<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
