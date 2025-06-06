<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html?role=Admin');
    exit();
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "❌ 缺少比賽 ID";
    exit();
}

// Supabase 設定
$projectUrl = 'https://fdkhwqwtjentmuzwhokc.supabase.co';
$apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZka2h3cXd0amVudG11endob2tjIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDg1MTE5NjksImV4cCI6MjA2NDA4Nzk2OX0.ZHijq5e612BceVP5fHLXSBaZF6vNXpOq5Hw5dzz7J8M';

$bucket = 'competitions';
$dataUrl = "$projectUrl/rest/v1/Competitions";

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $poster_url = $_POST['original_poster']; // 預設使用原本圖片
    $poster_message = '⚠️ 未重新上傳圖片，沿用原本海報圖';

    // ✅ 若重新上傳圖片才處理 Storage 上傳
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['poster']['tmp_name'];
        $name = uniqid() . '_' . basename($_FILES['poster']['name']);
        $uploadPath = "Poster/$name";
        $uploadUrl = "$projectUrl/storage/v1/object/$bucket/$uploadPath";

        $fileData = file_get_contents($tmp);
        $mime = mime_content_type($tmp);

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
            $poster_url = "$projectUrl/storage/v1/object/public/$bucket/$uploadPath";
            $poster_message = '✅ 已成功更新圖片';
        } else {
            $error = [
                'code' => $uploadStatus,
                'response' => $uploadResponse
            ];
        }
    }

    // 更新資料表
    if (!$error) {
        $data = [
            'Title' => $title,
            'Description' => $description,
            'Poster' => $poster_url,
            'Start_date' => $start_date,
            'End_date' => $end_date
        ];
        $url = "$dataUrl?Id=eq." . urlencode($id);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "apikey: $apiKey",
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            echo "<script>alert('比賽資料已更新。" . $poster_message . "'); window.location.href='./main.php';</script>";
            exit();
        } else {
            $error = [
                'code' => $status,
                'response' => $response
            ];
        }
    }
} else {
    // 取得比賽原始資料
    $url = "$dataUrl?Id=eq." . urlencode($id);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
        "Accept: application/json"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $competition = $result[0] ?? null;

    if (!$competition) {
        echo "❌ 查無此比賽";
        exit();
    }
}
?>

<!-- ✅ 編輯表單 -->
<!DOCTYPE html>
<html lang="zh-TW">

    <head>
        <meta charset="UTF-8">
        <title>編輯比賽</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style>
            body {
                background-color: #f4f4f9;
                padding-top: 50px;
                font-family: Arial;
            }
            .container {
                max-width: 600px;
                background-color: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            h2 {
                text-align: center;
                color: #2c3e50;
            }
            .preview {
                max-width: 100%;
                margin-bottom: 15px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h2>編輯比賽資訊</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <h5>❌ 更新失敗</h5>
                    <p><strong>HTTP 狀態：</strong> <?= $error['code'] ?></p>
                    <pre><?= htmlspecialchars($error['response']) ?></pre>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?= urlencode($id) ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">比賽標題</label>
                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($competition['Title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">比賽說明</label>
                    <textarea class="form-control" name="description" rows="4" required><?= htmlspecialchars($competition['Description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>目前海報</label><br>
                    <?php if (!empty($competition['Poster'])): ?>
                        <img src="<?= htmlspecialchars($competition['Poster']) ?>" class="preview" alt="比賽海報">
                    <?php else: ?>
                        <p class="text-muted">尚未上傳圖片</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="poster">重新上傳圖片（可選）</label>
                    <input type="file" class="form-control-file" name="poster" accept="image/*">
                    <input type="hidden" name="original_poster" value="<?= htmlspecialchars($competition['Poster']) ?>">
                </div>

                <div class="form-group">
                    <label for="start_date">起始日期</label>
                    <input type="date" class="form-control" name="start_date" value="<?= $competition['Start_date'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">結束日期</label>
                    <input type="date" class="form-control" name="end_date" value="<?= $competition['End_date'] ?>" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">更新比賽</button>
                <div class="text-center mt-3">
                    <a href="./main.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </body>

</html>
