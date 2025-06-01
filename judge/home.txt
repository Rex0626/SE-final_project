<?php
// 啟動 session
session_start();

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

// 查詢資料庫，獲取發布日期、公告內容和海報
$sql = "
SELECT 
    postdate, 
    word,
    paper
FROM 
    post
ORDER BY 
    postdate DESC;  -- 按照發布日期降序排列
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 資料查詢成功，逐條取得資料
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = [
            'postdate' => $row['postdate'],
            'word' => $row['word'],
            'paper' => $row['paper'] // 新增海報
        ];
    }
} else {
    $posts = []; // 如果沒有資料
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>高雄大學激發學生創意競賽管理系統－首頁</title>
</head>

<style type="text/css">
	body{
		margin: 0;
		width: 100%;
		height: 100%;
		background-color: #ffffff;
		background-image: linear-gradient(315deg, #ffffff 0%, #d7e1ec 74%);
		background-attachment: fixed;
		background-size: cover;
	}
	.header {
		text-align: center;
		color: #595859;
		font-family: Microsoft JhengHei;
		margin: 20px 0;
	}
	.container {
		display: flex;
		justify-content: center;
		align-items: flex-start;
		gap: 50px; 
	}
	.announcement{
		table th, td {
			border-bottom: 1px dashed #c0c0c2;
		}
		table th:first-child, td:first-child {
			width: 100px;
		}
	}
	.announcement img {
		max-width: 200px; /* 控制圖片最大寬度 */
		max-height: 150px; /* 控制圖片最大高度 */
		display: block;
		margin: 0 auto;
	}
</style>

<body>
	<div class="header">
		<h1 style="text-align:center">國立高雄大學「激發學生創意競賽」</h1>
		<h1 style="text-align:center">資料管理系統</h1>
	</div>
	<div class="container">
		<table>
			<tr>
				<th colspan="4"><h3><font color="#5f5e63">請選擇登入身分</font></h3></th>
			</tr>
			<tr>
				<td align='center'><a href="administrator/administrator.php">管理員</a>．</td>
				<td align='center'><a href="project/st/login.php">學生</a>．</td>
				<td align='center'><a href="project/tr/login.php">指導老師</a>．</td>
				<td align='center'><a href="judge/judge.php">評審委員</a></td>
			</tr>
			<tr>
				<td align='center'><a href="sign_up.php">前往報名</a></td> <!-- 新增此列 -->
			</tr>
		</table>
		<table class="announcement" width="600px">
			<tr>
				<th colspan="4"><h3><font color="#5f5e63">最新消息</font></h3></th>
			</tr>
			<tr>
				<th>發布日期</th>
				<th>公告內容</th>
				<th>海報</th>
			</tr>
			<?php if (empty($posts)) { ?>
				<tr>
					<td colspan="3" align="center">目前沒有公告。</td>
				</tr>
			<?php } else {
				foreach ($posts as $post) { ?>
					<tr>
						<td align="center"><?php echo htmlspecialchars($post['postdate']); ?></td>
						<td><?php echo nl2br(htmlspecialchars($post['word'])); ?></td>
						<td align="center">
							<?php if (!empty($post['paper'])) { ?>
								<img src="<?php echo htmlspecialchars($post['paper']); ?>" alt="海報">
							<?php } else { ?>
								無海報
							<?php } ?>
						</td>
					</tr>
				<?php } 
			} ?>
		</table>
	</div>
</body>
</html>


