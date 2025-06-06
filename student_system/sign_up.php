<?php
session_start();
// 假設你在 login.php 成功登入後已經把 user_id 和 team_id 存進 $_SESSION
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// 引入 Supabase 連線設定
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>上傳作品</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body>
<?php include 'header.php'; // 上面寫的導覽列 ?>

<div class="container mt-4">
  <h2>上傳參賽作品</h2>
  <form action="upload_submission.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="file" class="form-label">選擇檔案：</label>
      <input class="form-control" type="file" id="file" name="submission_file" required>
    </div>
    <div class="mb-3">
      <label for="file_type" class="form-label">檔案類別：</label>
      <select class="form-select" id="file_type" name="file_type" required>
        <option value="proposal">企劃書</option>
        <option value="code">程式碼</option>
        <option value="poster">海報</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">上傳</button>
  </form>
</div>

<!-- Bootstrap JS -->
<script
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
