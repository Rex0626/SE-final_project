<?php
session_start();

// ✅ 只有系統管理員能使用
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

// 表單送出處理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $role = $_POST['role']; ; 

    $data = array(
        'Email' => $email,
        'Password' => $password,
        'Name' => $name,
        'Phone' => $phone,
        'Role' => $role
    );

    $json_data = json_encode($data);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'apikey: ' . $apiKey,
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 201) {
        echo "<script>alert('✅ 新增成功！'); window.location.href = './main.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ 新增失敗，請檢查輸入或 API。');</script>";
    }
}
?>

<!-- ✅ 參與者新增表單 -->
<!DOCTYPE html>
<html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <title>新增比賽資訊</title>
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
                color: #2c3e50;
            }
            .btn-primary {
                background-color: #3498db;
                border-color: #3498db;
            }
            .btn-primary:hover {
                background-color: #2980b9;
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
        <h2>新增評審帳號</h2>
        <form action="add.php" method="POST">
            <div class="form-group">
                <label for="name">姓名</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">電子郵件</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">電話</label>
                <input type="text" class="form-control" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">密碼</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="form-group">
                <label for="role">角色</label>
                <select class="form-control" name="role" required>
                    <option value="Student">學生</option>
                    <option value="Teacher">老師</option>
                    <option value="Judge" selected>評審</option>
                    <option value="Admin">管理員</option>
                </select>
            </div>

            <button type="submit" class="btn glow-on-hover center btn-block">新增參與者</button>
        </form>

        <div class="text-center mt-3">
            <a href="./main.php" class="btn btn-secondary">返回參與者管理介面</a>
        </div>
    </div>
</body>
</html>