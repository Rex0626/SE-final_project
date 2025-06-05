<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected'])) {
    $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';
    $baseUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co/rest/v1/Announcement';

    $selectedIds = $_POST['selected'];
    $idList = implode(',', array_map(fn($id) => '"' . $id . '"', $selectedIds));
    $url = $baseUrl . "?select=AnnouncementID,Title,Year&AnnouncementID=in.($idList)";

    // 初始化curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $announcements = json_decode($response, true);

    if (!is_array($announcements)) {
        die("❌ 公告資料取得錯誤");
    }

    // 檢查是否有公告資料
    var_dump($response);
    var_dump($announcements);
    
    // 產生首頁公告 HTML 範例改寫，加入 AnnouncementID 帶入連結
    $html = '';
    foreach ($announcements as $a) {
        $title = htmlspecialchars($a['Title']);
        $date = htmlspecialchars(substr($a['Year'], 0, 10));
        $id = htmlspecialchars($a['AnnouncementID']);  // 加入 ID
        $html .= "<li class=\"news-item\"><a href=\"admin_system/manage_announcement/announcement_detail.php?id={$id}\">{$title}</a><span class=\"news-date\">{$date}</span></li>\n";
    }



    // 寫入首頁可載入的檔案（路徑依專案調整）
    $filePath = __DIR__ . '/homepage_announcements.html';
    if (file_put_contents($filePath, $html) === false) {
        die("❌ 寫入首頁公告檔案失敗");
    }

    // 發佈成功後導回公告管理頁（可加參數顯示成功訊息）
    header('Location: ./main.php?status=success');
    exit();

} else {
    die("❌ 未選擇任何公告");
}
