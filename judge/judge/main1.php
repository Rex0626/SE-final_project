<?php
// 啟動 session
session_start();

// 檢查是否登入成功，並取得 jname
//if (!isset($_SESSION['jname'])) {
 //   // 如果沒有登入，重新導向到登入頁面
 //   header("Location: judge.php");
 //   exit;
//}

$jid = $_SESSION['jid'];

// 處理登出請求
if (isset($_GET['logout'])) {
    session_unset(); // 清除所有 session 變數
    session_destroy(); // 結束 session
    header("Location: judge.php"); // 導向到登入頁面
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>評審委員系統</title>
    <style type="text/css">
        /* 全域樣式 */
        body {
            margin: 0;
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #fffffc;
            background-image: linear-gradient(315deg, #fffffc 0%, #dbd2c1 74%);
            background-attachment: fixed;
            background-size: cover;
            font-family: Microsoft JhengHei;
        }

        /* 頁首樣式 */
        .fixed-header {
            background-color: #f7f5f2;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .fixed-header h1 {
            margin: 0;
            padding: 10px 20px;
            font-size: 36px;
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
            font-size: 20px;
            background-color: #f7f5f2;
            border-top: 1px solid #dbd2c1;
        }
        ul.drop-down-menu li {
            flex: 1;
            text-align: center;
            position: relative;
            white-space: nowrap;
        }
        ul.drop-down-menu a {
            display: block;
            color: #333;
            padding: 10px;
            text-decoration: none;
        }
        ul.drop-down-menu a:hover {
            background-color: #b5a68d;
            color: #fff;
        }
        ul.drop-down-menu li ul {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            background-color: #f7f5f2;
            border: 1px solid #dbd2c1;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        ul.drop-down-menu li:hover ul {
            display: block;
        }
        ul.drop-down-menu li ul li {
            text-align: center;
        }

        /* 主內容樣式 */
        .main-content {
            flex: 1;
            padding: 20px;
            text-align: center;
        }

        /* 頁腳樣式 */
        .footer {
            background-color: #f7f5f2;
            color: #635031;
            font-size: 14px;
            padding: 10px;
            text-align: center;
        }

        /* 登出按鈕樣式 */
        .logout-btn {
            background-color: #b5a68d;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 20px;
        }
        .logout-btn:hover {
            background-color: #946f3a;
        }
    </style>
</head>
<body>
    <!-- 頁首 -->
    <div class="fixed-header">
        <h1>評審委員系統</h1>
        <ul class="drop-down-menu">
            <li>
                <a href="score.php">上傳評分</a>
            </li>
            <li>
                <a href="#">個人資訊</a>
                <ul>
                    <li><a href="check.php">檢視資訊</a></li>
                    <li><a href="correct.php">修改資訊</a></li>
                </ul>
            </li>
            <li>
                <a href="past.php">歷屆作品瀏覽</a>
            </li>
        </ul>
    </div>

    <!-- 主內容 -->
    <div class="main-content">
        <h2>歡迎使用評審系統</h2>
        <p>請從上方選單選取您想執行的操作。</p>
        <!-- 登出按鈕 -->
        <a href="?logout=true">
            <button class="logout-btn">登出</button>
        </a>
    </div>

    <!-- 頁腳 -->
    <div class="footer">
        <p>© 2025 評審委員系統. 保留所有權利.</p>
    </div>
</body>
</html>

