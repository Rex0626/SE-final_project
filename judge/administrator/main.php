<?php
// 啟動 session
session_start();

// 檢查是否登入成功，並取得 jname
if (!isset($_SESSION['jname'])) {
    // 如果沒有登入，重新導向到登入頁面
    header("Location: administrator.php");
    exit;
}

$jname = $_SESSION['jname'];
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>管理者介面</title>
</head>

<style type="text/css">
	body {
		margin: 0;
		width: 100%;
		height: 100%;
		background-color: #fffffc;
		background-image: linear-gradient(315deg, #fffffc 0%, #dbd2c1 74%);
		background-attachment: fixed;
		background-size: cover;
	}
	.fixed-header {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		background-color: #f7f5f2;
		z-index: 1000;
	}
	.fixed-header h1 {
		margin: 0;
		padding: 10px 20px;
		font-size: 36px;
		font-family: Microsoft JhengHei;
		color: #635031;
		text-align: center;
	}

	ul {
		margin: 0;
		padding: 0;
		list-style: none;
	}
	ul.drop-down-menu {
		width: 100%;
		display: flex;
		font-family: Microsoft JhengHei;
		font-size: 20px;
	}
	ul.drop-down-menu li {
		flex: 1;
		position: relative;
		white-space: nowrap;
		text-align: center;
	}
	ul.drop-down-menu>li:last-child {
		border-right: none;
	}
	ul.drop-down-menu a {
		color: #333;
		display: block;
		padding: 0 30px;
		text-decoration: none;
		line-height: 40px;
	}
	ul.drop-down-menu a:hover {
		background-color: #b5a68d;
		color: #fff;
	}
	ul.drop-down-menu li:hover>a {
		background-color: #b5a68d;
		color: #fff;
	}
	ul.drop-down-menu ul {
		position: absolute;
		z-index: 99;
		left: 0;
		top: 100%;
		min-width: 180px;
	}
	ul.drop-down-menu ul li:last-child {
		border-bottom: none;
	}
	ul.drop-down-menu ul {
		display: none;
	}
	ul.drop-down-menu li:hover>ul {
		display: block;
	}

	.main-content {
		margin-top: 200px;
		padding: 20px;
	}

	.logout-button {
		margin: 20px auto;
		text-align: center;
	}
	.logout-button button {
		background-color: #b5a68d;
		border: none;
		color: white;
		font-size: 18px;
		padding: 10px 20px;
		border-radius: 5px;
		cursor: pointer;
	}
	.logout-button button:hover {
		background-color: #946f3a;
	}
</style>

<body>
	<div class="fixed-header">
		<h1>管理者使用介面</h1>
		<ul class="drop-down-menu">
			<li>
				<a href="check_teams.php">報名隊伍檢視</a>
			</li>
			<li>
				<a href="check_judge.php">評審資料檢視</a>
			</li>
			<li>
				<a href="#">競賽資料檢視</a>
				<ul>
					<li>
						<a href="check_score.php">評分資料</a>
					</li>
					<li>
						<a href="award.php">得獎名單檢視</a>
					</li>
				</ul>
			</li>
			<li>
				<a href="#">公告</a>
				<ul>
					<li>
						<a href="check_post.php">檢視已有公告</a>
					</li>
					<li>
						<a href="add_post.php">發布公告</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>

	
</body>
</html>

