<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// 🔐 顯示登入帳號
echo "<p>🔐 登入中的 Email：" . htmlspecialchars($_SESSION['email']) . "</p>";

// 🔧 Supabase 設定
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $yearInput = $_POST['year'];
    $adminEmail = $_SESSION['email'];

    // ✅ 若欄位 Year 是 DATE 型別，處理為 YYYY-MM-DD 格式
    $year = strlen($yearInput) > 4 ? substr($yearInput, 0, 4) . '-01-01' : $yearInput . '-01-01';

    // 📤 組成要送出的資料
    $data = [
        'Admin_Email' => $adminEmail,
        'Title' => $title,
        'Content' => $content,
        'Year' => $year
    ];

    $ch = curl_init($baseUrl . '/Announcement');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 201) {
        echo "<script>alert('✅ 公告新增成功！'); window.location.href = './main.php';</script>";
        exit();
    } else {
        echo "<h3 style='color:red;'>❌ 新增失敗，HTTP 狀態碼：$httpcode</h3>";
        echo "<pre>🧾 錯誤訊息：" . htmlspecialchars($response) . "</pre>";
    }
}
?>

<!-- HTML 表單區 -->
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增公告</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #FFF9F4;
            padding-top: 50px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 550px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .glow-on-hover {
    width: 220px;
    height: 50px;
    border: none;
    outline: none;
    color: #fff;
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
    <div class="container">
        <h2>新增公告</h2>
        <form method="POST">
            <div class="form-group">
                <label>公告標題</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>公告內容</label>
                <textarea name="content" class="form-control" rows="6" required></textarea>
            </div>
            <div class="form-group">
                <label>年度</label>
                <input type="date" name="year" class="form-control" required>
            </div>
            <button type="submit" class="btn glow-on-hover center btn-block">新增公告</button>
        </form>
        <div class="text-center mt-3">
            <a href="./main.php" class="btn btn-secondary">返回公告管理</a>
        </div>
    </div>
</body>
</html>