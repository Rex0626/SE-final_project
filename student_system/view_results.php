<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['team_id'])) {
  header('Location: login.php');
  exit;
}

$teamId = $_SESSION['team_id'];

// 1) 取 WorkID
$resA = $supabase
  ->from('All-Teams')
  ->select('WorkID, CompetitionId')
  ->eq('TeamID', $teamId)
  ->single()
  ->execute();
if ($resA->getStatus() !== 200) {
  exit('讀取隊伍作品失敗：' . json_encode($resA->getBody()));
}
$workId       = $resA->getBody()['WorkID'];
$competitionId= $resA->getBody()['CompetitionId'];

// 2) 撈作品資訊
$resW = $supabase
  ->from('Works')
  ->select('Description, Poster, VideoLink, CodeLink, created_at')
  ->eq('WorkID', $workId)
  ->single()
  ->execute();
$work = $resW->getBody();

// 3) 撈評分明細
$resE = $supabase
  ->from('Evaluations')
  ->select('Score, Comments, created_at')
  ->eq('WorkID', $workId)
  ->order('created_at', 'asc')
  ->execute();
$evals = $resE->getBody();

// 計算平均分
$total = 0;
foreach ($evals as $e) { $total += floatval($e['Score']); }
$avg = count($evals) ? round($total / count($evals), 2) : 0;

// 4) 撈最新公告（可按 CompetitionId 或 Year 篩選）
$resAnn = $supabase
  ->from('Announcement')
  ->select('Title, Content, Year')
  ->order('created_at', 'desc')
  ->limit(5)
  ->execute();
$anns = $resAnn->getBody();
?>

<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>我的作品與成績</title></head>
<body>
  <h1>作品資訊</h1>
  <p><?= htmlspecialchars($work['Description']) ?></p>
  <img src="<?= htmlspecialchars($work['Poster']) ?>" width="200"><br>
  <a href="<?= htmlspecialchars($work['VideoLink']) ?>" target="_blank">觀看影片</a> |
  <a href="<?= htmlspecialchars($work['CodeLink']) ?>" target="_blank">檢視程式碼</a>
  <hr>

  <h2>評分明細 (平均分：<?= $avg ?>)</h2>
  <?php if (empty($evals)): ?>
    <p>尚無評分</p>
  <?php else: ?>
    <ul>
      <?php foreach ($evals as $e): ?>
        <li>
          分數：<?= htmlspecialchars($e['Score']) ?>　
          評語：<?= htmlspecialchars($e['Comments'] ?? '—') ?>　
          (<?= date('Y-m-d H:i', strtotime($e['created_at'])) ?>)
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <hr>

  <h2>最新公告／得獎資訊</h2>
  <?php if (empty($anns)): ?>
    <p>目前沒有公告</p>
  <?php else: ?>
    <ul>
    <?php foreach ($anns as $a): ?>
      <li>
        <strong><?= htmlspecialchars($a['Title']) ?></strong>
        (<?= htmlspecialchars(substr($a['Year'],0,4)) ?>)<br>
        <?= nl2br(htmlspecialchars($a['Content'])) ?>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</body>
</html>
