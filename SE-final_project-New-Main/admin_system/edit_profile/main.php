<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// 取得目前登入者 email
$email = $_SESSION['email'];
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';

$error = null;

// === 提交更新資料 ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        "Name" => $_POST['name'],
        "Phone" => $_POST['phone']
    ];
    if (!empty($_POST['password'])) {
        $data['Password'] = $_POST['password'];  // 不加密，直接儲存明碼
    }


    $url = $baseUrl . '?Email=eq.' . urlencode($email);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        header("Location: edit_profile.php?success=1");
        exit();
    } else {
        $error = [
            'code' => $httpcode,
            'response' => $response
        ];
    }
} else {
    // === 抓取原始資料 ===
    $url = $baseUrl . '?Email=eq.' . urlencode($email);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $users = json_decode($response, true);
    $user = $users[0] ?? null;

    if (!$user) {
        echo "❌ 查無此使用者";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>修改個人資料</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            padding-top: 50px;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
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
<body style="background-color: #FFF9F4;">
    <div class="container">
        <h2>修改個人資料</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" role="alert">
                ✅ 資料更新成功！
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading">❌ 更新失敗</h5>
                <p><strong>HTTP 狀態碼：</strong> <?= $error['code'] ?></p>
                <p><strong>API 回應：</strong></p>
                <pre><?= htmlspecialchars($error['response']) ?></pre>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>姓名</label>
                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required>
            </div>
            <div class="form-group">
                <label>電話</label>
                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['Phone']) ?>" required>
            </div>
            <div class="form-group">
                <label>電子郵件（不可修改）</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['Email']) ?>" disabled>
            </div>
            <div class="form-group">
                <label>新密碼（留空代表不更改）</label>
                <input type="password" class="form-control" name="password">
            </div>
            <button type="submit" class="btn glow-on-hover center btn-block">儲存修改</button>
        </form>

        <div class="text-center mt-3">
            <a href="../admin_system.php" class="btn btn-secondary">返回主選單</a>
        </div>
    </div>
</body>
</html>