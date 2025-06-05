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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        header {
            padding: 40px 20px 30px;
            text-align: center;
            color: white;
            font-weight: 900;
            font-size: 2.5rem;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        header p {
            font-size: 1.1rem;
            margin-top: 10px;
            font-weight: 500;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        main.container {
            max-width: 400px;
            margin: 0 auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 25px;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 14px;
            padding: 30px 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: flex;
            justify-content: center;
        }
        .card a.btn {
            background-color: #007bff;
            color: white;
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }
        .card a.btn:hover {
            background-color: #0056b3;
        }
        .logout-container {
            max-width: 400px;
            margin: 30px auto 50px;
            text-align: center;
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
            padding: 16px 50px;
            font-size: 1.3rem;
            font-weight: 700;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            text-decoration: none;
            display: inline-block;
            width: 100%;
            max-width: 320px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-logout:hover {
            background-color: #a71d2a;
        }

    </style>

</head>

<body>
    <header>
        <h1>高雄大學激發學生創意競賽管理系統</h1>
        <p>歡迎，<?php echo htmlspecialchars($_SESSION['email']); ?>！</p>
    </header>

    <main class="container">
        <div class="card">
            <a href="./manage_team/main.php" class="btn">隊伍管理</a>
        </div>
        <div class="card">
            <a href="./manage_participant/main.php" class="btn">參賽者資料管理</a>
        </div>
        <div class="card">
            <a href="./manage_announcement/main.php" class="btn">公告發布與管理</a>
        </div>
        <div class="card">
            <a href="./award_summary/main.php" class="btn">整合得獎名單</a>
        </div>
        <div class="card">
            <a href="./edit_profile/main.php" class="btn">修改個人資料</a>
        </div>
    </main>

    <div class="logout-container">
        <a href="../logout.php" class="btn-logout">登出</a>
    </div>

    <footer>
        <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
    </footer>

    <script src="../scripts.js"></script>
</body>
</html>
