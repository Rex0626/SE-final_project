<?php
// 初始化錯誤訊息變數
$error_message = "";

// 檢查是否有表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // MySQL 資料庫連線設定
    $servername = "localhost";
    $username = "root"; // 替換為 MySQL 使用者名稱
    $password = "12345678"; // 替換為 MySQL 密碼
    $dbname = "judge"; // 替換為實際資料庫名稱

    // 建立連線
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 檢查連線是否成功
    if ($conn->connect_error) {
        die("連線失敗: " . $conn->connect_error);
    }

    // 取得表單資料
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 查詢 judge_data 資料表
    $sql = "SELECT * FROM administer WHERE aid = ? AND aname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 登入成功，取得 aname
        $row = $result->fetch_assoc();
        $aname = $row['aname'];

        // 開始 session 並存儲 aname
        session_start();
        $_SESSION['jname'] = $aname;

        // 登入成功，跳轉到 main.php
        header("Location: main.php");  
        exit;
    } else {
        // 登入失敗
        $error_message = "帳號或密碼錯誤，請重新輸入！";
    }
    

    // 關閉連線
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>管理員登入</title>
</head>

<style type="text/css">

	body{
	  margin: 0;
	  width: 100%;
	  height: 100vh;
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  background-color: #ffffff;
	  background-image: linear-gradient(315deg, #ffffff 0%, #d7e1ec 74%);
	}
	.container {
	  width: 300px;
	  height: 330px;
	  border-radius: 5px;
	  display: flex;
	  flex-direction: column;
	  align-items: center;
	}
	.container_header {
		margin: 10px auto;
		color: #595859;
		font-family: Microsoft JhengHei;
	}
	.form {
	  display: flex;
	  height: 300px;
	  width: 100%;
	  flex-direction: column;
	  justify-content: flex-end;
	  align-items: center;
	}
	.form--input {
	  width: 200px;
	  background-color: transparent;
	  border: 0;
	  border-bottom: 1px dashed #c2c3c4;
	  margin-bottom: 30px;
	  color: black;
	  height: 30px;
	}
	.form--btn {
	  background-color: transparent;
	  border: 0;
	  color: #595859;
	  height: 50px;
	  width: 100%;
	}
	.form--btn:hover{
	  opacity: 0.8;
	  background-color: rgba(189, 184, 212, 0.8);
	}
	.form--link {
	  margin-top: 10px;
	  font-size: 14px;
	  color: #595859;
	  text-decoration: none;
	}
	.form--link:hover {
	  text-decoration: underline; /* 滑鼠移上時加底線 */
	}

</style>

<body>

	<div class="container">
		<div class="container_header">
			<h1 align="center">
			管理員登入
				</h1>
			</div>
        <form action="" method="post" class="form">
    <input type="text" class="form--input" name="username" placeholder="帳號">
    <input type="password" class="form--input" name="password" placeholder="密碼">
    <button type="submit" class="form--btn">登入</button>
    <a href="forget_password.html" class="form--link">忘記密碼？</a>
</form>
	    </div>

</body>
</html>