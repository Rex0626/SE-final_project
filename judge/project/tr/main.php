<?php
session_start();
require_once 'config.php'; // 資料庫連接設定

// 假設老師已經登入，透過 Session 取得老師 ID
$teacher_id = $_SESSION['teacher_id'];

// 查詢老師指導的隊伍
$query_team = "SELECT * FROM team WHERE Team_ID IN (SELECT Team_ID FROM teacher WHERE Teacher_ID = ?)";
$stmt_team = $conn->prepare($query_team);
$stmt_team->bind_param("s", $teacher_id);
$stmt_team->execute();
$result_team = $stmt_team->get_result();

// 查詢隊伍內的學生資料，包含所有需要顯示的欄位
$query_students = "SELECT Stu_ID, Stu_Name, Stu_Department_Grade, Stu_Phone, Stu_Email, Team_ID FROM student WHERE Team_ID = ?";
$stmt_students = $conn->prepare($query_students);

// 查詢隊伍的作品資料，包含所有需要顯示的欄位
$query_works = "SELECT Work_ID, Work_Name, Work_Description, Work_Code, Work_Video, Work_Poster, Team_ID FROM works WHERE Team_ID = ?";
$stmt_works = $conn->prepare($query_works);

// 初始化資料
$teams = [];
$students = [];
$works = [];

// 取得隊伍資料
while ($row_team = $result_team->fetch_assoc()) {
    $teams[] = $row_team;
    
    // 取得學生資料
    $stmt_students->bind_param("s", $row_team['Team_ID']);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
    $students[$row_team['Team_ID']] = $result_students->fetch_all(MYSQLI_ASSOC);
    
    // 取得作品資料
    $stmt_works->bind_param("s", $row_team['Team_ID']);
    $stmt_works->execute();
    $result_works = $stmt_works->get_result();
    $works[$row_team['Team_ID']] = $result_works->fetch_all(MYSQLI_ASSOC);
}

$stmt_team->close();
$stmt_students->close();
$stmt_works->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>指導老師介面</title>
    <style type="text/css">
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7fa;
        color: #333;
    }

    .fixed-header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        padding: 20px;
        text-align: center;
    }

    .fixed-header h1 {
        margin: 0;
        font-size: 28px;
        color: #5f4b3d;
    }

    .drop-down-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: center;
        background-color: #ffffff;
        border-bottom: 1px solid #ddd;
    }

    .drop-down-menu li {
        position: relative;
        text-align: center;
        flex: none;
    }

    .drop-down-menu a {
        display: block;
        padding: 15px 25px;
        color: #5f4b3d;
        text-decoration: none;
        transition: background-color 0.3s;
        text-align: center;
    }

    .drop-down-menu a:hover {
        background-color: #b5a68d;
        color: #fff;
    }

    .main-content {
        margin-top: 100px;
        padding: 20px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .team-section {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 25px;
        transition: transform 0.3s;
        border-left: 4px solid #b5a68d;
    }

    .team-section:hover {
        transform: translateY(-8px);
    }

    .team-title {
        font-size: 26px;
        font-weight: bold;
        color: #5f4b3d;
        margin-bottom: 20px;
    }

    .student-list, .work-list {
        padding-left: 20px;
    }

    .student-item, .work-item {
        margin-bottom: 12px;
        font-size: 16px;
        line-height: 1.6;
    }

    .student-item span, .work-item span {
        color: #888;
    }

    .student-item {
        padding-left: 10px;
        padding-top: 5px;
        border-left: 3px solid #b5a68d;
    }

    .work-item {
        font-style: italic;
        color: #4a4a4a;
    }

    .work-item img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-top: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .drop-down-menu {
            flex-direction: column;
        }

        .drop-down-menu li {
            text-align: left;
        }

        .main-content {
            padding: 10px;
        }
    }

    .logout-button {
        position: absolute;
        right: 80px;
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

    img {
        max-width: 100%;
        border-radius: 5px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    img:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .tab-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 20px;
    }

	.work-item img {
    display: block; /* 確保圖片是區塊元素 */
    max-width: 80%; /* 最大寬度設定為 80%，避免圖片過大 */
    height: auto; /* 自動調整高度 */
    border-radius: 8px; /* 圓角效果 */
    margin: 10px auto; /* 上下邊距為 10px，左右自動居中 */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* 增加陰影 */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* 添加過渡效果 */
}

.work-item img:hover {
    transform: scale(1.1); /* 放大圖片至 1.1 倍 */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3); /* 增加陰影效果 */
}

</style>

</head>
<body>
    <div class="fixed-header">
        <h1>指導老師使用介面</h1>
        <ul class="drop-down-menu">
            <li><a href="#">指導隊伍資料</a></li>
            <li><a href="past.php">歷屆作品瀏覽</a></li>
        </ul>
        <a href="logout.php" class="logout-button">登出</a>
    </div>

    <div class="main-content">
        <?php foreach ($teams as $team): ?>
            <div class="team-section">
                <h3 class="team-title">隊伍名稱：<?php echo $team['Team_Name']; ?></h3>

                <div class="student-list">
                    <h4>隊伍成員：</h4>
                    <ul>
                        <?php foreach ($students[$team['Team_ID']] as $student): ?>
                            <li class="student-item">
                                <strong><?php echo $student['Stu_Name']; ?></strong> (學號: <?php echo $student['Stu_ID']; ?>)
                                <div>科系: <?php echo $student['Stu_Department_Grade']; ?> | 電話: <?php echo $student['Stu_Phone']; ?> | 電子郵件: <?php echo $student['Stu_Email']; ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="work-list">
                    <h4>作品檢視：</h4>
                    <div class="work-item">
                        <?php foreach ($works[$team['Team_ID']] as $work): ?>
                            <div class="work-item">
                                <p><strong><?php echo $work['Work_Name']; ?></strong> - <?php echo $work['Work_Description']; ?></p>
                                <a href="<?php echo $work['Work_Code']; ?>" target="_blank">程式碼</a> | 
                                <a href="<?php echo $work['Work_Video']; ?>" target="_blank">影片</a> | 
                                
                                <div>
    <img src="https://raw.githubusercontent.com/stu5544/pics/refs/heads/main/<?php echo basename($work['Work_Poster']); ?>" alt="作品海報">
</div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>















