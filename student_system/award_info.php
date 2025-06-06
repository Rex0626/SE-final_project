<?php
session_start();
// 這一頁大多不需要登入就能看（如果你要公開展示），不過如果要限制只有「登入學生」能看，就加下面一段：
// if (!isset($_SESSION['user_id'])) {
//   header('Location: login.php');
//   exit;
// }

require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>獲獎隊伍資訊</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h2>獲獎隊伍列表</h2>
  <?php
  // 假設我們想顯示排名前 3 名的隊伍
  $response = $supabase
    ->from('results')
    ->select('team_id, total_score, ranking')
    ->order('ranking', ['ascending' => true])
    ->limit(3)
    ->execute();

  if ($response->getStatus() === 200) {
    $winners = $response->getBody();
    if (count($winners) > 0) {
      echo "<table class='table table-bordered'>";
      echo "<thead><tr><th>名次</th><th>隊伍名稱</th><th>總分</th><th>隊員清單</th><th>作品連結</th></tr></thead><tbody>";

      foreach ($winners as $row) {
        $teamId = $row->team_id;
        $ranking = $row->ranking;
        $score   = $row->total_score;

        // 撈隊伍名稱
        $teamRes = $supabase
          ->from('teams')
          ->select('team_name, advisor_name')
          ->eq('id', $teamId)
          ->single()
          ->execute();
        $teamName = ($teamRes->getStatus() === 200) 
          ? htmlspecialchars($teamRes->getBody()->team_name) 
          : '未知隊伍';

        // 撈隊員清單（team_members）
        $membersRes = $supabase
          ->from('team_members')
          ->select('member_name, student_number')
          ->eq('team_id', $teamId)
          ->execute();
        $memberList = '';
        if ($membersRes->getStatus() === 200 && count($membersRes->getBody()) > 0) {
          $marr = array_map(function($m) {
            return htmlspecialchars($m->member_name . '（' . $m->student_number . '）');
          }, $membersRes->getBody());
          $memberList = implode('<br>', $marr);
        } else {
          $memberList = '無';
        }

        // 撈作品列表（submissions）— 顯示為「超連結文字」
        $subsRes = $supabase
          ->from('submissions')
          ->select('file_name, file_url, file_type')
          ->eq('team_id', $teamId)
          ->execute();
        $subLinks = '';
        if ($subsRes->getStatus() === 200 && count($subsRes->getBody()) > 0) {
          $sarr = array_map(function($s) {
            $fname = htmlspecialchars($s->file_name);
            $furl  = htmlspecialchars($s->file_url);
            return "<a href='{$furl}' target='_blank'>{$fname}</a>";
          }, $subsRes->getBody());
          $subLinks = implode('<br>', $sarr);
        } else {
          $subLinks = '尚未上傳';
        }

        echo "<tr>
                <td>{$ranking}</td>
                <td>{$teamName}</td>
                <td>{$score}</td>
                <td>{$memberList}</td>
                <td>{$subLinks}</td>
              </tr>";
      }

      echo "</tbody></table>";
    } else {
      echo "<div class='alert alert-info'>目前尚未有得獎隊伍。</div>";
    }
  } else {
    echo "<div class='alert alert-danger'>無法查詢得獎隊伍，請稍後重試。</div>";
  }
  ?>
</div>
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
