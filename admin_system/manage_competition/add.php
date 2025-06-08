<?php
session_start();

// ✅ 只有系統管理員能使用
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

// Supabase 設定
$projectUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

$bucket = 'competitions'; // 請確認 bucket 名稱正確
$dataUrl = $projectUrl . '/rest/v1/Competitions';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $poster_url = '';

    // ✅ 圖片處理
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $originalName = basename($_FILES['poster']['name']);
        $uniqueName = uniqid() . '_' . $originalName;
        $uploadUrl = "$projectUrl/storage/v1/object/$bucket/Poster/$uniqueName";

        $fileData = file_get_contents($fileTmpPath);
        $mime = mime_content_type($fileTmpPath);

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $apiKey",
            "Content-Type: $mime",
            "x-upsert: true"
        ));

        $uploadResponse = curl_exec($ch);
        $uploadStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($uploadStatus === 200 || $uploadStatus === 201) {
            $poster_url = "$projectUrl/storage/v1/object/public/$bucket/Poster/$uniqueName";
        } else {
            echo "<script>alert('❌ 圖片上傳失敗，HTTP 狀態碼：$uploadStatus');</script>";
            echo "<pre>Debug: $uploadResponse</pre>";
            $poster_url = '';
        }
        if (strtotime($start_date) >= strtotime($end_date)) {
            echo "<script>alert('❌ 開始日期不能晚於或等於結束日期'); window.history.back();</script>";
            exit();
        }
    }else {
    $poster_url = ''; // 沒有上傳圖片時，海報欄位留空
    }

    // ✅ 上傳比賽資料
    $data = array(
        'Title' => $title,
        'Description' => $description,
        'Poster' => $poster_url,
        'Start_date' => $start_date,
        'End_date' => $end_date
    );

    $json_data = json_encode($data);
    $ch = curl_init($dataUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 201) {
        echo "<script>alert('✅ 比賽新增成功！'); window.location.href = './main.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ 新增失敗，請檢查資料或 Supabase 設定');</script>";
    }
}
?>

<!-- ✅ 新增比賽表單 -->
<!DOCTYPE html>
<html lang="zh-TW">
    <head>
        <meta charset="UTF-8">
        <title>新增比賽資訊</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            body {
                background-color: #f4f4f9;
                padding-top: 50px;
                font-family: Arial, sans-serif;
            }
            .container {
                max-width: 550px;
                background-color: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #2c3e50;
            }
            .btn-primary {
                background-color: #3498db;
                border-color: #3498db;
            }
            .btn-primary:hover {
                background-color: #2980b9;
            }
        </style>
    </head>
    
    <body>
        <div class="container">
            <h2>新增比賽資訊</h2>
            <form action="add.php" method="POST" enctype="multipart/form-data" onsubmit="return validateTime()">
                <div class="form-group">
                    <label for="title">比賽標題</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">比賽說明</label>
                    <textarea class="form-control" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="poster">海報圖片檔案</label>
                    <input type="file" class="form-control-file" name="poster" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="start_date">起始日期</label>
                    <input type="date" id="start_date" class="form-control" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">結束日期</label>
                    <input type="date" id="end_date" class="form-control" name="end_date" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">➕ 新增比賽</button>
            </form>
            <div class="text-center mt-3">
                <a href="main.php" class="btn btn-secondary">返回比賽管理介面</a>
            </div>
        </div>
    </body>

    <script>
        function validateTime() {
            const start = new Date(document.getElementById('start_date').value);
            const end = new Date(document.getElementById('end_date').value);

            if (start >= end) {
                alert("❌ 開始時間不能晚於或等於結束時間，請重新輸入！");
                return false; // 阻止表單送出
            }
            return true;
        }
    </script>


</html>
