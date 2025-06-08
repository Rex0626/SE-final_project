<?php
session_start();

// 檢查是否登入
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

// Supabase 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M'; // ⬅️ 請換成你的實際 API Key

// 取得現有資料（透過 Email 查詢）
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
$userData = json_decode($response, true);

if (!isset($userData[0]['ParticipantID'])) {
    echo "<script>alert('查無此帳號'); history.back();</script>";
    exit();
}

$participantId = $userData[0]['ParticipantID'];

// 若為 POST 就執行更新
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    $updateFields = [
        "Name" => $name,
        "Phone" => $phone,
        "Email" => $email
    ];

    if (!empty($password)) {
        $updateFields["Password"] = $password;
    }

    $payload = json_encode($updateFields);

    $updateUrl = $apiUrl . '?ParticipantID=eq.' . urlencode($participantId);
    $ch = curl_init($updateUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $updateResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 204) {
        echo "<script>alert(' 資料更新成功！'); window.location.href='judge_system.php';</script>";
    } else {
        echo "<script>alert(' 資料更新失敗，HTTP 狀態碼：$httpCode');</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>修改個人資訊</title>
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
    }
    label {
      display: block;
      margin-bottom: 10px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    input[readonly] {
      background-color: #f5f5f5;
    }
    button[type="submit"] {
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
    }
    button[type="submit"]:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <header>
    <h1>修改個人資訊</h1>
    <a href="judge_system.php" class="back-btn">返回主頁</a>
  </header>
  <div class="container">
    <form method="POST">
      <label>姓名：
        <input type="text" name="name" value="<?= htmlspecialchars($userData[0]['Name']) ?>" required>
      </label>
      <label>電話：
        <input type="text" name="phone" value="<?= htmlspecialchars($userData[0]['Phone']) ?>" required>
      </label>
      <label>電子郵件：
        <input type="text" name="email" value="<?= htmlspecialchars($userData[0]['Email']) ?>" required>
      </label>
      <label>職稱（不可修改）：
        <input type="text" value="<?= htmlspecialchars($userData[0]['Role']) ?>" readonly>
      </label>
      <label>密碼：
        <input type="password" name="password" placeholder="若不修改請留空">
      </label>
      <button type="submit">確認修改</button>
    </form>
  </div>
</body>
</html>





