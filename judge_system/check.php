<?php
session_start();

//只有評審能進來
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'judge') {
    header("Location: ../login.php");
    exit();
}

$email = $_SESSION['email'];

//Supabase 設定
$apiUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Participants';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';  
$url = $apiUrl . '?Email=eq.' . urlencode($email);

// 呼叫 API
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . $apiKey,
    'Authorization: Bearer ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

//處理 JSON 回應
$data = json_decode($response, true);
if (is_array($data) && count($data) === 1) {
    $user = $data[0];
    $name = $user['Name'] ?? '';
    $phone = $user['Phone'] ?? '';
    $role = $user['Role'] ?? '';
    $email = $user['Email'] ?? '';
    $password = $user['Password'] ?? '';
} else {
    $error = '查無資料';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>評審委員資料</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Microsoft JhengHei", sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #635031;
        }
        .info {
            margin: 15px 0;
            font-size: 18px;
        }
        .back-btn {
  	    position: absolute;
  	    top: 20px;
  	    right: 130px; 
  	    background-color: #6c757d;
  	    color: white;
   	    padding: 10px 18px;
  	    border: none;
  	    border-radius: 5px;
  	    font-size: 14px;
  	    text-decoration: none;
  	    transition: background-color 0.3s;
	}
	.back-btn:hover {
  	    background-color: #5a6268;
	}

    </style>
</head>
<body>

<div class="container">
    <?php if (isset($error)): ?>
        <h2>發生錯誤</h2>
        <p style="color: red; text-align: center;"><?php echo $error; ?></p>
    <?php else: ?>
        <h2>歡迎，<?php echo htmlspecialchars($name); ?>!</h2>
	<a href="judge_system.php" class="back-btn">返回主頁</a>
        <p>以下是您的個人資料：</p>
        <div class="info"><strong>姓名：</strong> <?php echo htmlspecialchars($name); ?></div>
        <div class="info"><strong>電話：</strong> <?php echo htmlspecialchars($phone); ?></div>
        <div class="info"><strong>電子郵件：</strong> <?php echo htmlspecialchars($email); ?></div>
        <div class="info"><strong>職稱：</strong> <?php echo htmlspecialchars($role); ?></div>
        <div class="info"><strong>密碼：</strong> <?php echo htmlspecialchars($password); ?></div>
    <?php endif; ?>

    
</div>

</body>
</html>




