<?php
session_start();
if (!isset($_SESSION['team_id'])) {
  header('Location: login.php');
  exit;
}

require_once 'config.php'; // 裡面已初始化 $supabase = new SupabaseClient(...)

// 假設你在上傳表單裡用 <input name="work_description"> 作為作品名稱／簡述
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $teamId     = $_SESSION['team_id'];
  $description = trim($_POST['work_description']);

  // 1. 先在 Works 表新增一筆空資料，拿到 auto-generated 的 WorkID
  //    (如果你想先拿 WorkID，再去上傳檔名要存成 ${WorkID}.jpg 或類似，也可以先插一筆)
  $resInsertWork = $supabase
    ->from('Works')
    ->insert([
      'Description' => $description,
      // Poster/VideoLink/CodeLink 先保留空值，稍後再 update
    ])
    ->single()   // 單筆
    ->execute();

  if ($resInsertWork->getStatus() !== 201) {
    echo "無法在 Works 建立新作品：".json_encode($resInsertWork->getBody());
    exit;
  }

  // 拿到新建立的 WorkID
  $newWork = $resInsertWork->getBody();
  $workId  = $newWork->workid; // 注意：Supabase 會把回傳欄位自動小寫化

  // 2. 如果使用者有同時上傳海報、影片、程式碼... 這邊示範只上傳一個檔案（Poster）
  if (isset($_FILES['poster_file']) && is_uploaded_file($_FILES['poster_file']['tmp_name'])) {
    $fileTemp  = $_FILES['poster_file']['tmp_name'];
    $fileName  = $_FILES['poster_file']['name'];
    $bucket    = 'student-submissions';  // 請先在 Storage 建立好此 bucket

    // 假設我們想把海報檔存在「<teamId>/poster_<workId>.jpg」這個路徑
    $ext       = pathinfo($fileName, PATHINFO_EXTENSION);
    $storePath = "$teamId/poster_{$workId}." . $ext;
    $fileData  = file_get_contents($fileTemp);

    $upPosterRes = $supabase
      ->storage()
      ->from($bucket)
      ->upload($storePath, $fileData, ['upsert' => true]);

    if ($upPosterRes->getStatus() === 200) {
      // 取得公開 URL
      $urlRes = $supabase
        ->storage()
        ->from($bucket)
        ->getPublicUrl($storePath);

      if ($urlRes->getStatus() === 200) {
        $posterUrl = $urlRes->getBody()->publicUrl;

        // 3. 把 Poster 欄位更新到 Works 表
        $updWorkRes = $supabase
          ->from('Works')
          ->update(['Poster' => $posterUrl])
          ->eq('workid', $workId)
          ->execute();

        if ($updWorkRes->getStatus() !== 200) {
          echo "無法更新 Works 的 Poster：".json_encode($updWorkRes->getBody());
          exit;
        }
      } else {
        echo "無法取得 Poster 公開 URL：".json_encode($urlRes->getBody());
        exit;
      }
    } else {
      echo "Poster 檔案上傳失敗：".json_encode($upPosterRes->getBody());
      exit;
    }
  }

  // （同理，你可以在同一頁面裡用其他 <input type="file" name="code_file"> 來上傳 CodeLink、<input name="video_file"> 來上傳影片並更新 Works.CodeLink、Works.VideoLink）

  // 4. 最後，把 All-Teams 裡對應的隊伍的 WorkID 更新成剛剛的 $workId
  $updateTeamRes = $supabase
    ->from('All-Teams')
    ->update(['WorkID' => $workId])
    ->eq('TeamID', $teamId)
    ->execute();

  if ($updateTeamRes->getStatus() === 200) {
    echo "<div class='alert alert-success'>作品上傳並已儲存至資料庫！</div>";
  } else {
    echo "<div class='alert alert-danger'>無法更新隊伍 WorkID: ".json_encode($updateTeamRes->getBody())."</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>作品上傳</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<?php include 'header.php'; ?>

<div class="container mt-4">
  <h2>上傳參賽海報</h2>
  <form action="upload_submission.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="work_description" class="form-label">作品名稱／說明：</label>
      <input type="text" class="form-control" id="work_description" name="work_description" required>
    </div>
    <div class="mb-3">
      <label for="poster_file" class="form-label">海報（Poster）：</label>
      <input class="form-control" type="file" id="poster_file" name="poster_file" required>
    </div>
    <button type="submit" class="btn btn-primary">提交作品</button>
  </form>
</div>

<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>