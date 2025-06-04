<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

if (!isset($_GET['work_id'])) {
    echo "❌ 未提供作品 ID";
    exit();
}

$workId = $_GET['work_id'];

// Supabase 設定
$baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

function fetch($table, $filter, $select = '*') {
    global $baseUrl, $apiKey;
    $url = $baseUrl . $table . '?select=' . urlencode($select) . '&' . $filter;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey"
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    if (!is_array($data) || count($data) === 0) {
        echo "<pre>❌ 找不到作品資訊：\n";
        var_dump($res);
        exit();
    }
    return $data[0]; // 只抓一筆
}

$work = fetch('Works', 'WorkID=eq.' . $workId, 'Description,Poster,VideoLink,CodeLink,updated_at');

$posterPath = ltrim($work['Poster'] ?? '', '/');
$videoPath = ltrim($work['VideoLink'] ?? '', '/');


?>


<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>作品詳細資料</title>
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="../../styles.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
                margin: 0;
                padding: 0;
            }
            header {
                background-color: #4CAF50;
                color: white;
                padding: 20px;
                text-align: center;
            }
            main {
                background-color: white;
                max-width: 600px;
                margin: 40px auto;
                padding: 30px;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h2 {
                color: #4CAF50;
                border-bottom: 2px solid #4CAF50;
                padding-bottom: 8px;
            }
            ul {
                list-style: none;
                padding: 0;
            }
            li {
                margin: 12px 0;
            }
            .btn {
                display: inline-block;
                margin-top: 20px;
                padding: 8px 14px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 6px;
                text-decoration: none;
                font-size: 14px;
                cursor: pointer;
            }
            .btn:hover {
                background-color: #45a049;
            }
        </style>
    </head>

    <body>
        <header>
            <h1>作品詳細資料</h1>
            <p>歡迎，<?php echo htmlspecialchars($_SESSION['email']); ?>！</p>
        </header>


        <main>
            <h2>作品資訊</h2>
            <ul>
                <li><strong>作品描述 : </strong><?= htmlspecialchars($work['Description']) ?></li>
                <li><strong>📌 Poster：</strong>
                    <?php if (!empty($work['Poster'])): ?>
                        <img src="<?= htmlspecialchars($posterPath) ?>" alt="作品海報" style="max-width: 100%; height: auto;">
                    <?php else: ?>
                        （無）
                    <?php endif; ?>
                </li>

                <li><strong>🎬 影片連結：</strong>
                    <?php if (!empty($work['VideoLink'])): ?>
                        <video width="100%" controls>
                            <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                            您的瀏覽器不支援影片播放。
                        </video>
                    <?php else: ?>
                        （無）
                    <?php endif; ?>
                </li>

                <li><strong>💻 程式碼連結：</strong>
                    <?= $work['CodeLink'] ? "<a href='" . htmlspecialchars($work['CodeLink']) . "' target='_blank'>查看程式碼</a>" : '（無）' ?>
                </li>

                <li><strong>🕒 更新時間：</strong><?= htmlspecialchars($work['updated_at']) ?></li>
            
            </ul>

            <a class="btn" href="javascript:history.back()">← 返回</a>
        
        </main>


    </body>


    <footer>
        <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
    </footer>

    <script src="../../scripts.js"></script>


</html>


