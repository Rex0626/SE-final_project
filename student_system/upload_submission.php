<?php
session_start();
if (!isset($_SESSION['team_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php'; // 已在裡面初始化 $supabase 及 generateUUID()

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamId      = $_SESSION['team_id'];
    $description = trim($_POST['work_description']);
    $workId      = generateUUID();

    // 1) 在 Works 表新增一筆初始紀錄
    $resInsert = $supabase
        ->from('Works')
        ->insert([[
            'WorkID'      => $workId,
            'Description' => $description,
        ]])
        ->execute();

    if ($resInsert->getStatus() !== 201) {
        exit('建立作品失敗：' . json_encode($resInsert->getBody()));
    }

    // 2) 處理海報海報上傳到 Storage 並取得公開 URL
    $posterUrl = '';
    if (isset($_FILES['poster_file']) && is_uploaded_file($_FILES['poster_file']['tmp_name'])) {
        $bucket   = 'student-submissions'; // 請先在 Supabase Storage 建立此 bucket
        $tmpPath  = $_FILES['poster_file']['tmp_name'];
        $origName = $_FILES['poster_file']['name'];
        $ext      = pathinfo($origName, PATHINFO_EXTENSION);
        $storePath= "$teamId/poster_{$workId}." . $ext;
        $fileData = file_get_contents($tmpPath);

        // Upload to Storage
        $upRes = $supabase
            ->storage()
            ->from($bucket)
            ->upload($storePath, $fileData, ['upsert' => true]);

        if ($upRes->getStatus() === 200) {
            // 取得公開 URL
            $urlRes = $supabase
                ->storage()
                ->from($bucket)
                ->getPublicUrl($storePath);

            if ($urlRes->getStatus() === 200) {
                $posterUrl = $urlRes->getBody()->publicUrl;
            } else {
                exit('無法取得 Poster URL：' . json_encode($urlRes->getBody()));
            }
        } else {
            exit('海報檔案上傳失敗：' . json_encode($upRes->getBody()));
        }
    }

    // 3) 更新 Works 這筆紀錄的 Poster 欄位
    if ($posterUrl) {
        $resUpd = $supabase
            ->from('Works')
            ->update([['Poster' => $posterUrl]])
            ->eq('WorkID', $workId)
            ->execute();

        if ($resUpd->getStatus() !== 200) {
            exit('更新作品海報連結失敗：' . json_encode($resUpd->getBody()));
        }
    }

    // 4) 把隊伍的 WorkID 更新回 All-Teams
    $resTeam = $supabase
        ->from('All-Teams')
        ->update([['WorkID' => $workId]])
        ->eq('TeamID', $teamId)
        ->execute();

    if ($resTeam->getStatus() !== 200) {
        exit('更新隊伍作品關聯失敗：' . json_encode($resTeam->getBody()));
    }

    $message = '作品上傳完成！';
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>作品上傳</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body>
  <div class="container py-5">
    <h1 class="mb-4">提交作品</h1>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form action="upload_submission.php" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="work_description" class="form-label">作品說明</label>
        <input
          type="text"
          class="form-control"
          id="work_description"
          name="work_description"
          required
        >
      </div>

      <div class="mb-3">
        <label for="poster_file" class="form-label">海報 (Poster)</label>
        <input
          class="form-control"
          type="file"
          id="poster_file"
          name="poster_file"
          accept="image/*"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary">提交作品</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
