<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統管理員子系統 (SAS)</title>
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="../styles.css">
</head>

<body>
    <header>
        <h1>高雄大學激發學生創意競賽管理系統</h1>
        <p>歡迎，<?php echo htmlspecialchars($_SESSION['email']); ?>！</p>
    </header>

    <main>
        <ul>
            <li><a href="./manage_team/main.php">隊伍管理</a></li>
            <li><a href="./manage_judge/main.php">管理評審資料與權限</a></li>
            <li><a href="./manage_announcement/main.php">發布與管理公告</a></li>
            <li><a href="award_summary.php">整合與產生得獎名單</a></li>
            <li><a href="./edit_profile/main.php">修改個人資料</a></li>
            <li><a href="../logout.php" style="color: red;">登出</a></li>
        </ul>
    </main>

    <footer>
        <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
    </footer>

    <script src="../scripts.js"></script>
</body>
</html>
