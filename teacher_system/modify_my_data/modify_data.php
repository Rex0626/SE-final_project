<script>
// 閒置時間上限：10 分鐘（單位：毫秒）
const timeoutDuration = 10 * 1000;

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
        window.location.href = "../../login.html?role=Teacher";
    }
}

// 監聽使用者互動事件來重設計時器
['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
    window.addEventListener(evt, resetTimer);
});
</script>

<?php
session_start();
require_once '../db_connect.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'teacher') {
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
    echo "<p>找不到該老師的資料，請確認 Email 是否正確</p>";
    exit();
}
$teacher = $teacherData[0];
$teacherID = $teacher['ParticipantID'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $newEmail = $_POST['email'] ?? '';
    $newPassword = $_POST['password'] ?? '';  // 新增

    if (empty($name) || empty($newEmail)) {
        $message = "姓名與電子郵件不可為空";
    } else {
        // 👉 檢查 email 是否已經被其他人使用
        list($emailCheckRes, $emailCheckCode) = callAPI("$baseUrl/Participants?select=*&Email=eq.$newEmail", 'GET', null, $apiKey);
        $existingEmailData = json_decode($emailCheckRes, true);

        // 如果有找到資料，且不是自己的帳號，代表重複
        if (is_array($existingEmailData) && count($existingEmailData) > 0 && $existingEmailData[0]['ParticipantID'] !== $teacherID) {
            $message = "此電子郵件已被其他帳號使用，請使用其他 Email。";
        } else {
            $updateData = [
                'Name' => $name,
                'Phone' => $phone,
                'Email' => $newEmail
            ];

            $passwordChanged = false;
            if (!empty($newPassword)) {
                $updateData['Password'] = $newPassword;  // 明文儲存（⚠️ 不安全）
                $passwordChanged = true;
            }

            list($updateRes, $updateCode) = callAPI("$baseUrl/Participants?ParticipantID=eq.$teacherID", 'PATCH', $updateData, $apiKey);
            if ($updateCode === 204) {
                // 若 email 或密碼有變化，就登出
                if ($newEmail !== $teacherEmail || $passwordChanged) {
                    session_destroy();
                    echo "<script>alert('您的 Email 或密碼已變更，請重新登入'); window.location.href = '../../login.html?role=Teacher';</script>";
                    exit();
                } else {
                    $message = "更新成功！";
                    list($res, $code) = callAPI("$baseUrl/Participants?select=*&Email=eq.$teacherEmail", 'GET', null, $apiKey);
                    $teacherData = json_decode($res, true);
                    $teacher = $teacherData[0];
                }
            } else {
                $message = "更新失敗，請稍後再試";
            }
        }
    }
}


?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <title>修改個人資料</title>
    <link rel="stylesheet" href="../styles.css" />
</head>
<body>
<header>
    <h1>高雄大學激發學生創意競賽指導教師系統</h1>
</header>

<nav>
    <ul class="drop-down-menu">
        <li><a href="../view_my_data/view_data.php">瀏覽隊伍資料</a></li>
        <li><a href="../view_rank/view_rank.php">瀏覽競賽資料</a></li>
        <li><a href="modify_data.php">瀏覽與修改個人資料</a></li>
    </ul>
</nav>

<a href="../../logout.php" class="logout-button">登出</a>

<div class="main-content">
    <?php if ($message): ?>
        <p class="message <?= strpos($message, '成功') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="modify_data.php">
        <label for="name">姓名：</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($teacher['Name'] ?? '') ?>" required />

        <label for="phone">電話：</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($teacher['Phone'] ?? '') ?>" />

        <label for="email">電子郵件：</label>
<input type="email" id="email" name="email" value="<?= htmlspecialchars($teacher['Email'] ?? '') ?>" required />

        <label for="password">新密碼（如需修改）：</label>
<input type="password" id="password" name="password" placeholder="留空表示不修改" />


        <button type="submit">更新資料</button>
    </form>
</div>


<footer>
    <p>&copy; 2024 國立高雄大學 - 激發學生創意競賽指導教師系統</p>
</footer>

</body>
</html>
