<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

if (!isset($_GET['team_id'])) {
    echo "❌ 未提供隊伍 ID";
    exit();
}

$teamId = $_GET['team_id'];


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
    if (!is_array($data)) {
        echo "<pre>❌ Supabase 回傳錯誤：\n";
        var_dump($res);
        exit();
    }
    return $data;
}

// 第一步：從 Team-Members 表找出對應 ParticipantID
$members = fetch('Team-Members', 'TeamID=eq.' . $teamId, 'ParticipantID');

// 把所有 ID 拿出來
$participantIds = array_map(function ($m) {
    return $m['ParticipantID'];
}, $members);

if (empty($participantIds)) {
    echo "⚠️ 找不到此隊伍的成員";
    exit();
}

// 第二步：根據 ParticipantID 清單查詢成員資料
$idList = implode(',', array_map(function($id) {
    return '"' . $id . '"';
}, $participantIds));

$students = fetch('Participants', "ParticipantID=in.($idList)", 'ParticipantID,Name,Email,Phone,Role');

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>隊伍成員詳細資訊</title>
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
            max-width: 700px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
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
    <h1>隊伍成員詳細資訊</h1>
    <p>歡迎，<?= htmlspecialchars($_SESSION['email']) ?>！</p>
</header>

<main>
    <h2>成員清單</h2>
    <table>
        <thead>
            <tr>
                <th>姓名</th>
                <th>Email</th>
                <th>電話</th>
                <th>角色</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['Name']) ?></td>
                    <td><?= htmlspecialchars($s['Email']) ?></td>
                    <td><?= htmlspecialchars($s['Phone']) ?></td>
                    <td><?= htmlspecialchars($s['Role']) ?></td>
                
                    <td>
                        <a class="btn" href="./edit.php?participant_id=<?= urlencode($s['ParticipantID']) ?>&team_id=<?= urlencode($teamId) ?>">✏️ 編輯 </a>
                        <a class="btn" href="./delete.php?participant_id=<?= urlencode($s['ParticipantID']) ?>&team_id=<?= urlencode($teamId) ?>"
                        onclick="return confirm('確定要刪除這位成員嗎？');">🗑️ 刪除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a class="btn" href="./main.php">← 返回</a>
</main>

<footer>
    <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽管理系統</p>
</footer>

</body>
</html>
