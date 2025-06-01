<?php
session_start();
require_once 'config.php'; // 資料庫連接設定

// 確保使用者已經登入
if (!isset($_SESSION['team_id'])) {
    header("Location: login.php");
    exit();
}

// 取得隊伍的資料
$team_id = $_SESSION['team_id'];
$team_name = $_SESSION['team_name'];

// 生成並存儲 CSRF Token
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 生成隨機的 token
}

function displayMessage($message) {
    echo "<script>document.querySelector('.fixed-header').insertAdjacentHTML('afterend', '<div style=\"text-align: center; color: red;\">$message</div>');</script>";
    echo "<script>alert('$message');</script>";
}

// 檢查 CSRF Token
function validateCSRF() {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        displayMessage("CSRF Token 驗證失敗。");
        exit();
    }
}

// 處理新增學生
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    validateCSRF();

    $stu_id = trim($_POST['stu_id']);
    $stu_name = trim($_POST['stu_name']);
    $stu_department_grade = trim($_POST['stu_department_grade']);
    $stu_phone = trim($_POST['stu_phone']);
    $stu_email = trim($_POST['stu_email']);

    if (!empty($stu_id) && !empty($stu_name) && !empty($stu_department_grade) && !empty($stu_phone) && !empty($stu_email)) {
        // 檢查 Stu_ID 是否已存在
        $check_query = "SELECT 1 FROM student WHERE Stu_ID = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $stu_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                displayMessage("該學生 ID 已存在，請使用不同的 ID！");
            } else {
                // 新增學生資料
                $add_query = "INSERT INTO student (Stu_ID, Team_ID, Stu_Name, Stu_Department_Grade, Stu_Phone, Stu_Email) VALUES (?, ?, ?, ?, ?, ?)";
                if ($add_stmt = $conn->prepare($add_query)) {
                    $add_stmt->bind_param("ssssss", $stu_id, $team_id, $stu_name, $stu_department_grade, $stu_phone, $stu_email);
                    if ($add_stmt->execute()) {
                        displayMessage("學生資料已成功新增！");
                    } else {
                        displayMessage("資料庫新增失敗：" . $add_stmt->error);
                    }
                    $add_stmt->close();
                }
            }
            $stmt->close();
        }
    } else {
        displayMessage("請完整填寫所有欄位。");
    }
}

// 處理刪除學生資料
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    validateCSRF();

    $stu_id = $_POST['delete_student'];
    $delete_query = "DELETE FROM student WHERE Stu_ID = ? AND Team_ID = ?";
    if ($stmt = $conn->prepare($delete_query)) {
        $stmt->bind_param("ss", $stu_id, $team_id);
        if ($stmt->execute()) {
            displayMessage("學生資料已刪除。");
        } else {
            displayMessage("刪除失敗，請再次操作。");
        }
        $stmt->close();
    }
}

// 處理隊伍名稱修改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_name'])) {
    validateCSRF();

    $new_team_name = trim($_POST['team_name']);
    if (strlen($new_team_name) < 3 || strlen($new_team_name) > 100) {
        displayMessage("隊伍名稱長度必須在 3 到 100 個字元之間。");
    } else {
        $update_query = "UPDATE team SET Team_Name = ? WHERE Team_ID = ?";
        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("ss", $new_team_name, $team_id);
            if ($stmt->execute()) {
                $team_name = $new_team_name; // 更新隊伍名稱
                displayMessage("隊伍名稱已更新！");
            } else {
                displayMessage("更新失敗，請再次操作！");
            }
            $stmt->close();
        }
    }
}

// 查詢該隊伍的學生資料
$query = "SELECT * FROM student WHERE Team_ID = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("s", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    echo "資料庫錯誤: " . $conn->error;
}

// 查詢該隊伍的參賽作品
$works_query = "SELECT * FROM works WHERE Team_ID = ?";
if ($stmt = $conn->prepare($works_query)) {
    $stmt->bind_param("s", $team_id);
    $stmt->execute();
    $works_result = $stmt->get_result();
    $works = $works_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    echo "資料庫錯誤: " . $conn->error;
}

// 處理修改作品描述
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_work_description']) && isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        displayMessage("無效的請求，請重新提交。");
        exit();
    }

    $work_id = $_POST['edit_work_description'];
    $new_description = trim($_POST['new_description']);

    // 檢查描述是否有效
    if (strlen($new_description) < 3 || strlen($new_description) > 500) {
        displayMessage("作品描述長度應該在 3 到 500 個字元之間。");
    } else {
        // 更新作品描述
        $update_query = "UPDATE works SET Work_Description = ? WHERE Work_ID = ? AND Team_ID = ?";
        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("sss", $new_description, $work_id, $team_id);
            if ($stmt->execute()) {
                displayMessage("作品描述已更新！");
            } else {
                displayMessage("更新作品描述失敗，請稍後再試！");
            }
            $stmt->close();
        } else {
            displayMessage("準備語句失敗：" . $conn->error);
        }
    }
}


// 關閉資料庫連接
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>學生介面</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f0f0, #d9c7b6);
            background-attachment: fixed;
            color: #333;
            line-height: 1.6;
        }

        /* 固定頭部樣式 */
        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .fixed-header h1 {
            margin: 0;
            text-align: center;
            font-size: 28px;
            color: #4d4d4d;
        }

        .navbar {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 10px;
        }

        .navbar a {
            font-size: 16px;
            color: #555;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #f7f5f2;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #b5a68d;
            color: #fff;
        }

        .logout-button {
            position: absolute;
            right: 20px;
            top: 30px;
            font-size: 16px;
            text-decoration: none;
            color: #fff;
            background-color: #d9534f;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #c9302c;
        }

        /* 主內容樣式 */
        .main-content {
            margin-top: 120px;
            padding: 30px;
        }

        h2 {
            color: #635031;
            text-align: center;
            margin-bottom: 20px;
        }

        /* 表格樣式 */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f7f5f2;
            color: #4d4d4d;
            text-align: center;
            padding: 12px;
            font-weight: bold;
        }

        td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            color: #555;
        }

        /* 表單樣式 */
        input[type="text"], input[type="email"], button {
            padding: 10px;
            margin: 5px 0;
            font-size: 16px;
            width: 100%;
            max-width: 350px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }

        input[type="text"]:focus, input[type="email"]:focus, button:hover {
            border-color: #b5a68d;
        }

        button {
            background-color: #635031;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #b5a68d;
        }

        /* 分頁選擇按鈕 */
        .tab-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab-buttons button {
            padding: 10px 15px;
            font-size: 16px;
            color: #fff;
            background-color: #635031;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .tab-buttons button:hover {
            background-color: #b5a68d;
        }

        .hidden {
            display: none;
        }

        /* 作品海報欄位樣式 */
        .poster-cell {
            text-align: center;
            background-color: #f7f5f2;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .poster-title {
            margin: 0 0 10px 0;
            font-weight: bold;
            font-size: 18px;
            color: #4d4d4d;
            text-transform: uppercase;
        }

        .poster-img {
            max-width: 500px;
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .poster-img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* 新增學生表單 */
        form {
            margin-bottom: 20px;
            text-align: center;
        }

        /* 訊息顯示 */
        .message {
            text-align: center;
            padding: 10px;
            background-color: #f7f5f2;
            margin: 20px auto;
            border: 1px solid #b5a68d;
            color: #635031;
            font-size: 16px;
            border-radius: 5px;
        }

	.hidden {
    display: none;
}

	form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-row {
    display: flex;
    gap: 10px;
}

.form-row input {
    flex: 1;
    min-width: 150px;  /* 可調整格子的最小寬度 */
    padding: 5px;
}

form button {
    padding: 10px;
    margin-top: 10px;
    width: 50%;
}



    </style>

	<script>
        function showTeamAndStudentData() {
    document.getElementById('team-student-data').classList.remove('hidden');
    document.getElementById('works-data').classList.add('hidden');
}

function showWorksData() {
    document.getElementById('works-data').classList.remove('hidden');
    document.getElementById('team-student-data').classList.add('hidden');
}

    </script>
</head>
<body>
    <div class="fixed-header">
    <h1>學生使用介面</h1>
    <div class="navbar">
        <a href="#" onclick="showTeamAndStudentData()">隊伍與學生資料</a>
        <a href="#" onclick="showWorksData()">參賽作品</a>
        <a href="past.php">歷屆作品瀏覽</a>
    </div>
    <a href="logout.php" class="logout-button">登出</a>
</div>

<div class="main-content">
    <div id="team-student-data">
        <h2>隊伍與學生資料</h2>
        <!-- 隊伍資料表格 -->
        <table>
            <thead>
                <tr>
                    <th>隊伍ID</th>
                    <th>隊伍名稱</th>
                    <th>修改隊伍名稱</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $team_id; ?></td>
                    <td><?php echo htmlspecialchars($team_name); ?></td>
                    <td><a href="edit_team_name.php?team_id=<?php echo $team_id; ?>">修改</a></td>
                </tr>
            </tbody>
        </table>

        <!-- 新增學生表單 -->
        <h3>新增學生</h3>
<form action="" method="post">
    <div class="form-row">
        <input type="text" name="stu_id" placeholder="學生ID" required>
        <input type="text" name="stu_name" placeholder="學生名稱" required>
        <input type="text" name="stu_department_grade" placeholder="系所/年級" required>
        <input type="text" name="stu_phone" placeholder="聯絡電話" required>
        <input type="email" name="stu_email" placeholder="電子郵件" required>
    </div>
    <div class="form-row">
        <button type="submit" name="add_student">新增學生</button>
    </div>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>


        <h3>隊伍成員</h3>
        <table>
            <thead>
                <tr>
                    <th>學生ID</th>
                    <th>學生名稱</th>
                    <th>系所/年級</th>
                    <th>聯絡電話</th>
                    <th>電子郵件</th>
                    <th>修改</th>
                    <th>刪除</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['Stu_ID']; ?></td>
                        <td><?php echo $student['Stu_Name']; ?></td>
                        <td><?php echo $student['Stu_Department_Grade']; ?></td>
                        <td><?php echo $student['Stu_Phone']; ?></td>
                        <td><?php echo $student['Stu_Email']; ?></td>
                        <td><a href="edit_student.php?student_id=<?php echo $student['Stu_ID']; ?>">修改</a></td>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <button type="submit" name="delete_student" value="<?php echo $student['Stu_ID']; ?>" onclick="return confirm('確認刪除這名學生嗎？')">刪除</button>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 參賽作品資料 -->
    <div id="works-data" class="hidden">
        <h2>參賽作品</h2>
        <table>
            <thead>
                <tr>
                    <th>作品ID</th>
                    <th>作品名稱</th>
                    <th>作品描述</th>
                    <th>程式碼連結</th>
                    <th>影片連結</th>
                    <th>修改描述</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td><?php echo $work['Work_ID']; ?></td>
                        <td><?php echo $work['Work_Name']; ?></td>
                        <td><?php echo $work['Work_Description']; ?></td>
                        <td><a href="<?php echo $work['Work_Code']; ?>" target="_blank">程式碼</a></td>
                        <td><a href="<?php echo $work['Work_Video']; ?>" target="_blank">影片</a></td>
                        <td><a href="edit_work_description.php?work_id=<?php echo $work['Work_ID']; ?>">修改描述</a></td>
                    </tr>
                    <tr>
                        <td colspan="6" class="poster-cell">
                            <p class="poster-title">作品海報</p>
                            <img src="https://raw.githubusercontent.com/stu5544/pics/refs/heads/main/<?php echo basename($work['Work_Poster']); ?>" alt="作品海報" class="poster-img">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>









