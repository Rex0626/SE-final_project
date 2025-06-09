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
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <style>
        body {
      font-family: "Microsoft JhengHei", sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #FFF9F4;
    }
    header {
        background: linear-gradient(135deg, #FFEFD4, #69503F); /* 改過ㄉ */
        color: white;
        padding: 30px 0;
        text-align: center;
        border-bottom: 5px solid #221F1F; /* 深咖啡邊框 */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    header h1 {
        margin: 0;
        font-size: 36px;
        color: #FFF9F4;
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

    .glow-on-hover {
    width: 220px;
    height: 50px;
    border: none;
    outline: none;
    color: #fff;
    font-size: 16px;
    background: #111;
    cursor: pointer;
    position: relative;
    z-index: 0;
    border-radius: 10px;
    }

  .glow-on-hover:before {
      content: '';
      background: linear-gradient(315deg, #d8853a 0%, #E5AA70 74%);
      position: absolute;
      top: -2px;
      left:-2px;
      background-size: 400%;
      z-index: -1;
      filter: blur(5px);
      width: calc(100% + 4px);
      height: calc(100% + 4px);
      animation: glowing 10s linear infinite;
      opacity: 0;
      transition: opacity .3s ease-in-out;
      border-radius: 10px;
  }

  .glow-on-hover:active {
      color: #000; 
  }

  .glow-on-hover:active:after {
      background: transparent;
  }

  .glow-on-hover:hover:before {
      opacity: 1;
  }

  .glow-on-hover:after {
      z-index: -1;
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: #E1C6A8;
      left: 0;
      top: 0;
      border-radius: 5px;
  }

  @keyframes glowing {
      0% { background-position: 0 0; }
      50% { background-position: 400% 0; }
      100% { background-position: 0 0; }
  }

  .center {
  max-width: fit-content;
  margin-left: auto;
  margin-right: auto;
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
      <button  class="btn glow-on-hover center btn-block">確認修改</button>
    </form>
  </div>
</body>
</html>