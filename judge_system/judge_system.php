<?php
session_start();

//檢查登入與角色
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

// 若網址有 ?logout=true 就登出
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../main.php");
    exit();
}

// 取得登入者資訊
$name = $_SESSION['name'];
$email = $_SESSION['email'];
// 如果尚未設定 judge_id，就連 Supabase 查詢一次
if (!isset($_SESSION['judge_id'])) {
    $apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
    $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

    $email = $_SESSION['email'];
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

    $result = json_decode($response, true);
    if (is_array($result) && count($result) > 0 && isset($result[0]['ParticipantID'])) {
        $_SESSION['judge_id'] = $result[0]['ParticipantID'];
    } else {
        echo "<script>alert('查無評審 ID，請重新登入'); window.location.href='login.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>評審委員系統</title>

  <style>
    header {
      background: linear-gradient(135deg, #ffcc00, #0056b3);
      color: white;
      padding: 30px 0;
      text-align: center;
      border-bottom: 5px solid #003366;
      position: relative;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    header h1 {
      margin: 0;
      font-size: 32px;
      color: black;
    }

    body {
      font-family: "Microsoft JhengHei", sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    }

    .logout-btn {
      position: absolute;
      top: 20px;
      right: 30px;
      background-color: #b5a68d;
      border: none;
      color: white;
      padding: 10px 18px;
      font-size: 14px;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .logout-btn:hover {
      background-color: #946f3a;
    }

    .card-menu {
      display: flex;
      justify-content: center;
      gap: 30px;
      padding: 40px 20px;
      flex-wrap: wrap;
    }

    .card-menu .card {
      background-color: #ffffff;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      text-align: center;
      font-size: 20px;
      color: #333;
      text-decoration: none;
      transition: transform 0.3s, background-color 0.3s;
      position: relative;
      cursor: pointer;
    }

    .card-menu .card:hover {
      transform: translateY(-5px);
      background-color: #f0f0f0;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      top: 100%;
      left: 50%;
      transform: translateX(-50%);
      background-color: white;
      border: 1px solid #ccc;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      min-width: 150px;
      z-index: 100;
      padding: 10px 0;
    }

    .card.dropdown:hover .dropdown-content {
      display: block;
    }

    .dropdown-content a {
      display: block;
      padding: 10px 20px;
      text-decoration: none;
      color: #333;
    }

    .dropdown-content a:hover {
      background-color: #eee;
    }

    .main-content {
      text-align: center;
      padding: 30px;
    }

    footer {
      background-color: #f7f5f2;
      text-align: center;
      padding: 15px 0;
      font-size: 14px;
      color: #333;
      margin-top: 40px;
      border-top: 2px solid #ffcc00;
    }
  </style>
</head>

<body>
  <header>
    <h1>高雄大學激發學生創意競賽管理系統</h1>
    <a href="../logout.php"><button class="logout-btn">登出</button></a>
  </header>

  <div class="card-menu">
    <a href="score.php" class="card">上傳評分</a>

    <div class="card dropdown">
      個人資訊
      <div class="dropdown-content">
        <a href="check.php">檢視資訊</a>
        <a href="correct.php">修改資訊</a>
      </div>
    </div>
  </div>

  <main class="main-content">
    <h2>歡迎 <?= htmlspecialchars($name) ?> 使用評審系統</h2>
    <p>請從上方選單選取您想執行的操作。</p>
    <p><strong>登入者 Participant ID：</strong> <?= htmlspecialchars($_SESSION['judge_id']) ?></p>

  </main>

  <footer>
    <p>&copy; 2025 國立高雄大學 - 激發學生創意競賽管理系統</p>
  </footer>
</body>
</html>



