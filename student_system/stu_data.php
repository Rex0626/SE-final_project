<script>
// 閒置時間上限：10 分鐘（單位：毫秒）
const timeoutDuration = 600 * 1000;

let timeoutHandle = setTimeout(showIdleWarning, timeoutDuration);

// 重設計時器函式
function resetTimer() {
    clearTimeout(timeoutHandle);
    timeoutHandle = setTimeout(showIdleWarning, timeoutDuration);
}

// 顯示提示視窗並導向登出
function showIdleWarning() {
    const userConfirmed = confirm("您已閒置超過 10 分鐘，系統即將登出。請按「確定」繼續。");
    if (userConfirmed) {
        window.location.href = "../../main.html?timeout=1";
    }
}

// 監聽使用者互動事件來重設計時器
['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
    window.addEventListener(evt, resetTimer);
});
</script>

<?php
session_start();
require_once 'stu_connect.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.html?role=Teacher');
    exit();
}

$teacherEmail = $_SESSION['email'];

// 先取得該老師的 ParticipantID 及目前資料
function callAPI($url, $method = 'GET', $data = null, $apiKey) {
    $ch = curl_init($url);
    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json",
        "Content-Type: application/json"
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($method === 'PATCH' && $data) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$res, $httpCode];
}

// 取得老師資料
list($res, $code) = callAPI("$baseUrl/Participants?select=*&Email=eq.$teacherEmail", 'GET', null, $apiKey);
$teacherData = json_decode($res, true);
if (!is_array($teacherData) || count($teacherData) === 0) {
    echo "<p>找不到您的資料，請確認 Email 是否正確</p>";
    exit();
}
$teacher = $teacherData[0];
$teacherID = $teacher['ParticipantID'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 從表單取得修改後資料
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // 簡單驗證（可擴充）
    if (empty($name)) {
        $message = "姓名不可為空";
    } else {
        // 更新資料到 Supabase
        $updateData = [
            'Name' => $name,
            'Phone' => $phone,
        ];

        list($updateRes, $updateCode) = callAPI("$baseUrl/Participants?ParticipantID=eq.$teacherID", 'PATCH', $updateData, $apiKey);
        if ($updateCode === 204) {
            $message = "更新成功！";
            // 重新抓取資料顯示最新內容
            list($res, $code) = callAPI("$baseUrl/Participants?select=*&Email=eq.$teacherEmail", 'GET', null, $apiKey);
            $teacherData = json_decode($res, true);
            $teacher = $teacherData[0];
        } else {
            $message = "更新失敗，請稍後再試";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <title>修改個人資料</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', sans-serif;
            padding-top: 60px;
        }
        .main-content {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .main-content h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #333;
        }
        label {
            margin-top: 15px;
            font-weight: 500;
            color: #444;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
            font-size: 1rem;
        }
        input[disabled] {
            background-color: #eee;
            cursor: not-allowed;
        }
        button[type="submit"] {
            width: 100%;
            margin-top: 25px;
            padding: 0.85rem;
            background-color: #007bff;
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background 0.2s, transform 0.1s;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        button[type="submit"]:active {
            transform: translateY(0);
        }

        .message {
            margin-bottom: 20px;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 500;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        a.back-btn {
            position: absolute;
            top: 1rem; right: 1rem;
            background: rgba(0,123,255,0.8);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background .2s;
        }
        a.back-btn:hover {
            background: rgba(0,123,255,1);
        }
    </style>
</head>

<body>
<a class="back-btn" href="student_system.html">← 回學生系統</a>
<div class="main-content">
    <h2>修改個人資料</h2>

    <?php if ($message): ?>
        <p class="message <?= strpos($message, '成功') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="student_data.php">
        <label for="name">姓名：</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($teacher['Name'] ?? '') ?>" required />

        <label for="phone">電話：</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($teacher['Phone'] ?? '') ?>" />

        <label for="email">電子郵件 (不可修改)：</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($teacher['Email'] ?? '') ?>" disabled />

        <button type="submit">更新資料</button>
    </form>
</div>
</body>

</html>