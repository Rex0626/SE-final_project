<?php
session_start();
include 'config_rest.php';

$team_id     = $_SESSION['team_id'] ?? exit('請先登入');
$bucket      = 'student-submissions';
$timestamp   = date('c');

// 假設只上傳一個海報檔
if (isset($_FILES['poster']['tmp_name']) && is_uploaded_file($_FILES['poster']['tmp_name'])) {
  $tmp   = $_FILES['poster']['tmp_name'];
  $orig  = $_FILES['poster']['name'];
  $ext   = pathinfo($orig, PATHINFO_EXTENSION);
  $key   = "{$team_id}/poster_".generateUUID().".{$ext}";
  $data  = file_get_contents($tmp);

  // 上傳到 Storage
  [$supload,$bupload] = supabaseRequest("storage/v1/object/{$bucket}/{$key}", 'POST', $data);
  if ($supload !== 200) exit("Storage upload failed");

  // 取得公開 URL
  [$surl,$burl] = supabaseRequest("storage/v1/url/{$bucket}/{$key}", 'GET');
  $url = $burl['publicUrl'] ?? '';

  // 更新 Works 表的 Poster 欄位
  [$supd,$bupd] = supabaseRequest('Works', 'PATCH', [
    'Poster'     => $url,
    'updated_at' => $timestamp
  ]);
  // 需加上 ?WorkID=eq.XYZ 作為 filter
  // 視 REST 配置不同，可能要改 endpoint → "Works?WorkID=eq.{$work_id}"
}
echo json_encode(['success'=>true,'url'=>$url]);
?>
