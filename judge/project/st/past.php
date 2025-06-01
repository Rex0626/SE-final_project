<?php
require_once 'config.php'; // 資料庫連接設定

// 查詢歷屆得獎作品
$works_query = "SELECT * FROM win_works"; // 用來查詢所有歷屆得獎作品
if ($stmt = $conn->prepare($works_query)) {
    $stmt->execute();
    $works_result = $stmt->get_result();
    $works = $works_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    echo "資料庫錯誤: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>歷屆得獎作品</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f0f0, #d9c7b6);
            background-attachment: fixed;
            color: #333;
            line-height: 1.6;
        }

        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #fff;
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .fixed-header h1 {
            margin: 0;
            text-align: center;
            font-size: 28px;
            color: #4d4d4d;
        }

        .main-content {
            margin-top: 100px;
            padding: 20px;
        }

        h2 {
            color: #635031;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f7f5f2;
            color: #4d4d4d;
            text-align: center;
            padding: 12px;
            font-weight: bold;
        }

        td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            color: #555;
        }

        img {
            max-width: 100%;
            border-radius: 5px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .poster-cell {
            text-align: center;
            background-color: #f7f5f2;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .poster-title {
            margin: 0 0 10px 0;
            font-weight: bold;
            font-size: 18px;
            color: #4d4d4d;
            text-transform: uppercase;
        }

        .poster-img {
            max-width: 500px;
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .poster-img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* 返回按鈕樣式 */
        .back-button {
            position: absolute;
            top: 20px;
            right: 20px;
            display: inline-block;
            background-color: #635031;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #b5a68d;
        }

	.fixed-header {
    position: fixed;
    top: 0;
    left: 50%;
    width: 100%; /* 修改為您需要的寬度 */
    transform: translateX(-50%); /* 使容器水平居中 */
    background-color: #fff;
    padding: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

.fixed-header h1 {
    margin: 0;
    text-align: center;
    font-size: 28px;
    color: #4d4d4d;
}

.back-button {
    position: absolute;
    top: 20px;
    right: 20px;
    display: inline-block;
    background-color: #635031;
    color: #fff;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 16px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.back-button:hover {
    background-color: #b5a68d;
}

    </style>
</head>
<body>

    <div class="fixed-header">
        <h1>歷屆得獎作品</h1>
	<a href="javascript:history.back()" class="back-button">返回</a>
    </div>
	
    <div class="main-content">
                <table>
            <thead>
                <tr>
                    <th>作品ID</th>
                    <th>作品名稱</th>
                    <th>作品描述</th>
                    <th>程式碼連結</th>
                    <th>影片連結</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td><?php echo $work['wwork_ID']; ?></td>
                        <td><?php echo $work['wwork_Name']; ?></td>
                        <td><?php echo $work['wwork_Description']; ?></td>
                        <td><a href="<?php echo $work['wwork_Code']; ?>" target="_blank">程式碼</a></td>
                        <td><a href="<?php echo $work['wwork_Video']; ?>" target="_blank">影片</a></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="poster-cell">
                            <p class="poster-title">作品海報</p>
                            <img src="https://raw.githubusercontent.com/stu5544/pics/refs/heads/main/<?php echo basename($work['wwork_Poster']); ?>" alt="作品海報" class="poster-img">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
</body>
</html>




