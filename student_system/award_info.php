<?php
// 不一定要登入才能看，如果你想公開顯示獲獎隊伍，就不用做 session 驗證。
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>獲獎隊伍列表</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<?php include 'header.php'; ?>
<div class="container mt-4">
  <h2>獲獎隊伍列表（前 3 名）</h2>
  <?php
  // 1. 先查 Evaluations 排名前 3 的隊伍
  $resTop3 = $supabase
    ->from('Evaluations')
    ->select('TeamID, total_score, ranking')
    ->order('ranking', ['ascending' => true])
    ->limit(3)
    ->execute();

  if ($resTop3->getStatus() === 200) {
    $top3 = $resTop3->getBody();
    if (count($top3) > 0) {
      echo "<table class='table table-bordered'>";
      echo "<thead>
              <tr>
                <th>名次</th>
                <th>隊伍名稱</th>
                <th>總分</th>
                <th>隊員清單</th>
                <th>作品連結</th>
              </tr>
            </thead><tbody>";

      foreach ($top3 as $row) {
        $teamId    = $row->teamid;
        $score     = $row->total_score;
        $ranking   = $row->ranking;

        // 2. 抓隊伍名稱
        $resTeam = $supabase
          ->from('All-Teams')
          ->select('TeamName')
          ->eq('TeamID', $teamId)
          ->single()
          ->execute();
        $teamName = ($resTeam->getStatus() === 200)
          ? htmlspecialchars($resTeam->getBody()->teamname)
          : '未知隊伍';

        // 3. 抓隊員清單：先從 Team-Members 拿 ParticipantID，再去 Participants 拿詳細資訊
        $resMembers = $supabase
          ->from('Team-Members')
          ->select('ParticipantID')
          ->eq('TeamID', $teamId)
          ->execute();

        $memberList = '';
        if ($resMembers->getStatus() === 200 && count($resMembers->getBody()) > 0) {
          $ids = array_map(function($r) { return $r->participantid; }, $resMembers->getBody());
          // 再批次向 Participants 撈出姓名、學號
          $resPart = $supabase
            ->from('Participants')
            ->select('ParticipantID, Name, StudentNumber')
            ->in('ParticipantID', $ids)
            ->execute();

          if ($resPart->getStatus() === 200) {
            $plist = array_map(function($p) {
              return htmlspecialchars($p->name . '（' . $p->studentnumber . '）');
            }, $resPart->getBody());
            $memberList = implode('<br>', $plist);
          } else {
            $memberList = '無法取得隊員資料';
          }
        } else {
          $memberList = '尚無隊員';
        }

        // 4. 抓作品連結：先從 All-Teams 拿這隊的 WorkID，再去 Works
        $resTeam2 = $supabase
          ->from('All-Teams')
          ->select('WorkID')
          ->eq('TeamID', $teamId)
          ->single()
          ->execute();
        $workLinks = '';
        if ($resTeam2->getStatus() === 200 && $resTeam2->getBody()->workid) {
          $workId = $resTeam2->getBody()->workid;
          $resWork = $supabase
            ->from('Works')
            ->select('Poster, VideoLink, CodeLink')
            ->eq('WorkID', $workId)
            ->single()
            ->execute();

          if ($resWork->getStatus() === 200) {
            $w = $resWork->getBody();
            // 把有值的部分做成超連結（例如 Poster 用圖片、CodeLink 用 “查看程式碼”）
            $linksArr = [];
            if (!empty($w->poster)) {
              $linksArr[] = "<a href='" . htmlspecialchars($w->poster) . "' target='_blank'>海報</a>";
            }
            if (!empty($w->videolink)) {
              $linksArr[] = "<a href='" . htmlspecialchars($w->videolink) . "' target='_blank'>影片</a>";
            }
            if (!empty($w->codelink)) {
              $linksArr[] = "<a href='" . htmlspecialchars($w->codelink) . "' target='_blank'>程式碼</a>";
            }
            $workLinks = !empty($linksArr) ? implode(' | ', $linksArr) : '未上傳';
          } else {
            $workLinks = '作品資料讀取失敗';
          }
        } else {
          $workLinks = '無作品';
        }

        echo "<tr>
                <td>{$ranking}</td>
                <td>{$teamName}</td>
                <td>{$score}</td>
                <td>{$memberList}</td>
                <td>{$workLinks}</td>
              </tr>";
      }

      echo "</tbody></table>";
    } else {
      echo "<div class='alert alert-info'>目前尚未有獲獎隊伍。</div>";
    }
  } else {
    echo "<div class='alert alert-danger'>查詢評分資料時失敗，狀態碼：" . $resTop3->getStatus() . "</div>";
  }
  ?>
</div>
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
