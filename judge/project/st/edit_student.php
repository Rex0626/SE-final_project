<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_GET['student_id'] ?? null;
if ($student_id) {
    // 查詢學生資料
    $query = "SELECT * FROM student WHERE Stu_ID = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
    } else {
        echo "資料庫錯誤: " . $conn->error;
    }
}

// 處理學生資料更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stu_name = $_POST['stu_name'];
    $stu_phone = $_POST['stu_phone'];
    $stu_email = $_POST['stu_email'];

    $update_query = "UPDATE student SET Stu_Name = ?, Stu_Phone = ?, Stu_Email = ? WHERE Stu_ID = ?";
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("ssss", $stu_name, $stu_phone, $stu_email, $student_id);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>學生資料已更新。</p>";
        } else {
            echo "<p style='color: red;'>更新失敗，請再試一次。</p>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改學生資料</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        label {
            font-size: 16px;
            margin-bottom: 10px;
            display: block;
            color: #555;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background-color: #5c9f5f;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #4a7c4a;
        }
        .back-button {
            background-color: #ccc;
            margin-top: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            display: block;
            width: 100%;
            text-align: center;
        }
        .back-button:hover {
            background-color: #bbb;
        }
        .message {
            margin: 20px 0;
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>修改學生資料</h2>

        <!-- 更新提示 -->
        <div class="message">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php if (isset($student)): ?>
                    <p style="color: green;">學生資料已成功更新！</p>
                <?php else: ?>
                    <p style="color: red;">更新失敗，請再試一次。</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- 表單 -->
        <form action="edit_student.php?student_id=<?php echo $student_id; ?>" method="POST">
            <label for="stu_name">學生名稱:</label>
            <input type="text" name="stu_name" value="<?php echo $student['Stu_Name']; ?>" required>
            
            <label for="stu_phone">聯絡電話:</label>
            <input type="text" name="stu_phone" value="<?php echo $student['Stu_Phone']; ?>" required>
            
            <label for="stu_email">電子郵件:</label>
            <input type="email" name="stu_email" value="<?php echo $student['Stu_Email']; ?>" required>
            
            <button type="submit">更新資料</button>
        </form>

        <!-- 返回按鈕 -->
        <a href="main.php" class="back-button">返回隊伍頁面</a>
    </div>
</body>
</html>


