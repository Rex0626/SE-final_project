<?php
// student_system/eval_list.php
session_start();
date_default_timezone_set('Asia/Taipei');
require_once 'config.php'; // callSupabase()

// 1) 先撈所有作品
$rW = callSupabase('Works?select=WorkID,Description', 'GET');
if ($rW['status'] !== 200) {
    die('作品讀取失敗');
}
$works = $rW['body'];

// 2) 再撈所有評分紀錄
$rE = callSupabase(
  'Evaluations?select=WorkID,JudgeID,Score,Comments,created_at&order=created_at.asc',
  'GET'
);
if ($rE['status'] !== 200) {
    die('評分讀取失敗');
}
$evals = $rE['body'];

// 3) 把評分依 WorkID 分組
$byWork = [];
foreach ($evals as $e) {
  $byWork[$e['WorkID']][] = $e;
}

// 4) 取評審名字對照
$rP = callSupabase('Participants?select=ParticipantID,Name', 'GET');
$judgeMap = [];
if ($rP['status'] === 200) {
  foreach ($rP['body'] as $p) {
    $judgeMap[$p['ParticipantID']] = $p['Name'];
  }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>所有作品評分列表</title>
  <style>
    /* Reset & 基本 */
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Noto Sans TC",sans-serif; background:#eef2f7; color:#333; }
    a { color: inherit; text-decoration: none; }
    /* 回主選單按鈕 */
    .btn-home {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background: #ff9800;
      color: #fff;
      padding: .75rem 1rem;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      font-weight: bold;
      transition: background .2s, transform .1s;
      z-index: 100;
    }
    .btn-home:hover {
      background: #fb8c00;
      transform: translateY(-2px);
    }

    /* Header */
    header {
      background: linear-gradient(120deg, #0066cc, #00aaff);
      color: #fff;
      padding: 1.5rem;
      text-align: center;
    }
    header h1 { font-size:2rem; letter-spacing:1px; }

    /* 容器 */
    .container { max-width:1000px; margin:2rem auto; padding:0 1rem; }

    /* 卡片 */
    .card {
      background:#fff;
      border-radius:12px;
      box-shadow:0 8px 24px rgba(0,0,0,0.08);
      margin-bottom:2rem;
      overflow:hidden;
      transition:transform .2s;
    }
    .card:hover { transform: translateY(-5px); }

    /* 卡片標題 */
    .card-header {
      background:#0066cc;
      padding:1rem 1.5rem;
    }
    .card-header h2 {
      margin:0;
      color:#fff;
      font-size:1.25rem;
      letter-spacing:.5px;
    }

    /* 卡片內容 */
    .card-body { padding:1.5rem; }

    /* 表格 */
    table { width:100%; border-collapse:collapse; }
    th, td {
      padding:.75rem 1rem;
      text-align:left;
    }
    thead th {
      background:#f4f6fa;
      font-weight:600;
      border-bottom:2px solid #e1e5eb;
    }
    tbody tr:nth-child(even) { background:#fafbfc; }
    tbody tr:hover { background:rgba(0,102,204,0.05); }

    /* 無資料提示 */
    .no-data {
      padding:1rem;
      color:#777;
      font-style:italic;
      text-align:center;
      background:#f9f9f9;
      border-radius:6px;
    }

    /* Footer */
    footer {
      text-align:center;
      padding:1rem;
      font-size:.9rem;
      color:#666;
    }

    /* 響應式 */
    @media (max-width:600px) {
      header h1 { font-size:1.5rem; }
      .card-header h2 { font-size:1rem; }
      th, td { padding:.5rem; font-size:.9rem; }
    }
  </style>
</head>
<body>
  <!-- 回主選單按鈕 -->
  <a href="student_system.html" class="btn-home">← 學生主選單</a>

  <header>
    <h1>所有作品評分列表</h1>
  </header>

  <div class="container">
    <?php foreach ($works as $w):
      $wid  = htmlspecialchars($w['WorkID']);
      $desc = htmlspecialchars($w['Description'] ?: '（無標題）');
      $list = $byWork[$wid] ?? [];
    ?>
      <div class="card">
        <div class="card-header">
          <h2>作品說明：<?= $desc ?></h2>
        </div>
        <div class="card-body">
          <?php if (count($list)): ?>
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>評審者</th>
                  <th>分數</th>
                  <th>評語</th>
                  <th>評審時間</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($list as $i => $e): ?>
                  <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($judgeMap[$e['JudgeID']] ?? $e['JudgeID']) ?></td>
                    <td><?= htmlspecialchars($e['Score']) ?></td>
                    <td><?= htmlspecialchars($e['Comments'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($e['created_at']) ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="no-data">尚無任何評分</div>
          <?php endif ?>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <footer>
    &copy; 2025 高雄大學 – 激發學生創意競賽管理系統
  </footer>
</body>
</html>
