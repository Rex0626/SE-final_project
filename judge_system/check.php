<?php
session_start();

// 檢查是否登入與角色
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

// Supabase API 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // ⬅️請換上你的實際 API Key

$email = $_SESSION['email'];

// 從 Supabase 取得資料
$queryUrl = $apiUrl . '?Email=eq.' . urlencode($email);
$ch = curl_init($queryUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

if (!isset($data[0])) {
    echo "<script>alert('找不到該使用者資料'); window.location.href='judge_system.php';</script>";
    exit();
}

$user = $data[0];
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>檢視個人資訊</title>
  <style>
    body {
      font-family: "Microsoft JhengHei", sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 20px;
    }
    header {
      background: linear-gradient(135deg, #ffcc00, #0056b3);
      color: white;
      padding: 30px 0;
      text-align: center;
      border-bottom: 5px solid #003366;
      position: relative;
    }
    header h1 {
      margin: 0;
      font-size: 28px;
      color: black;
    }
    .back-btn {
      position: absolute;
      top: 20px;
      right: 30px;
      background-color: #6c757d;
      border: none;
      color: white;
      padding: 10px 16px;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
    }
    .back-btn:hover {
      background-color: #5a6268;
    }
    .container {
      max-width: 500px;
      margin: 30px auto;
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .info {
      font-size: 18px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <header>
    <h1>檢視個人資訊</h1>
    <a href="judge_system.php" class="back-btn">返回主頁</a>
  </header>

  <div class="container">
    

    <div class="info"><strong>參與者 ID：</strong> <?= htmlspecialchars($user['ParticipantID']) ?></div>
    <div class="info"><strong>姓名：</strong> <?= htmlspecialchars($user['Name']) ?></div>
    <div class="info"><strong>電話：</strong> <?= htmlspecialchars($user['Phone']) ?></div>
    <div class="info"><strong>電子郵件：</strong> <?= htmlspecialchars($user['Email']) ?></div>
    <div class="info"><strong>職稱：</strong> <?= htmlspecialchars($user['Role']) ?></div>
    <div class="info"><strong>密碼：</strong> <?= htmlspecialchars($user['Password']) ?></div>
  </div>
</body>
</html>


