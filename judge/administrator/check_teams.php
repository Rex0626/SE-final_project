<?php
// 啟動 session
session_start();

// 檢查是否登入成功，並取得 aid
//if (!isset($_SESSION['jname'])) {
    // 如果沒有登入，重新導向到登入頁面
 //   header("Location: administrator.php");
 //   exit;
//}

// MySQL 資料庫連線設定
$servername = "localhost";
$username = "root"; // 替換為 MySQL 使用者名稱
$password = "12345678"; // 替換為 MySQL 密碼
$dbname = "judge"; // 替換為實際資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 查詢資料庫，獲取 team, student, works 和 teacher 的相關資料
$sql = "
SELECT 
    team.Team_ID, 
    team.Team_Name, 
    student.Stu_ID, 
    student.Stu_Name, 
    student.Stu_Department_Grade, 
    student.Stu_Phone, 
    student.Stu_Email, 
    works.Work_ID, 
    works.Work_Name, 
    works.Work_Description, 
    works.Work_Code, 
    works.Work_Video, 
    works.Work_Poster,
    teacher.Teacher_Name, 
    teacher.Teacher_Department, 
    teacher.Teacher_Phone, 
    teacher.Teacher_Email
FROM 
    team
LEFT JOIN 
    student ON team.Team_ID = student.Team_ID
LEFT JOIN 
    works ON team.Team_ID = works.Team_ID
LEFT JOIN 
    teacher ON team.Team_ID = teacher.Team_ID
ORDER BY 
    team.Team_ID;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 資料查詢成功，逐條取得資料
    $teams = [];
    while ($row = $result->fetch_assoc()) {
        $teams[$row['Team_ID']]['Team_Name'] = $row['Team_Name'];
        $teams[$row['Team_ID']]['students'][] = [
            'Stu_ID' => $row['Stu_ID'],
            'Stu_Name' => $row['Stu_Name'],
            'Stu_Department_Grade' => $row['Stu_Department_Grade'],
            'Stu_Phone' => $row['Stu_Phone'],
            'Stu_Email' => $row['Stu_Email']
        ];
        // 加入作品資料（避免重複）
        if (!isset($teams[$row['Team_ID']]['works'][$row['Work_ID']])) {
            $teams[$row['Team_ID']]['works'][$row['Work_ID']] = [
                'Work_Name' => $row['Work_Name'],
                'Work_Description' => $row['Work_Description'],
                'Work_Code' => $row['Work_Code'],
                'Work_Video' => $row['Work_Video'],
                'Work_Poster' => $row['Work_Poster']
            ];
        }
        // 加入指導老師資料（只添加一次）
        if (!isset($teams[$row['Team_ID']]['teacher'])) {
            $teams[$row['Team_ID']]['teacher'] = [
                'Teacher_Name' => $row['Teacher_Name'],
                'Teacher_Department' => $row['Teacher_Department'],
                'Teacher_Phone' => $row['Teacher_Phone'],
                'Teacher_Email' => $row['Teacher_Email']
            ];
        }
    }
} else {
    $error_message = "找不到任何資料";
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>隊伍資料</title>
    <style>
        body {
            font-family: Microsoft JhengHei;
            background-color: #f0f0f0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #635031;
        }
        .info {
            margin: 15px 0;
            font-size: 18px;
        }
        .back-btn {
            background-color: #b58c51;
            border: none;
            color: white;
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
        }
        .back-btn:hover {
            background-color: #946f3a;
        }
        hr {
            border: 1px solid #ddd;
        }
        img {
            max-width: 300px; /* 限制圖片的最大寬度 */
            max-height: 200px; /* 限制圖片的最大高度 */
            width: auto;
            height: auto;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>隊伍及相關資料</h2>
        <?php if (isset($error_message)) { echo "<p>$error_message</p>"; } ?>
        
        <?php foreach ($teams as $team_id => $team_data) { ?>
            <div class="info">
                <strong>隊伍編號：</strong> <?php echo htmlspecialchars($team_id); ?><br>
                <strong>隊伍名稱：</strong> <?php echo htmlspecialchars($team_data['Team_Name']); ?>
            </div>
            
            <div class="info">
                <strong>學生資料：</strong>
                <?php foreach ($team_data['students'] as $student) { ?>
                    <div class="info">
                        - 學生編號：<?php echo htmlspecialchars($student['Stu_ID']); ?><br>
                        - 姓名：<?php echo htmlspecialchars($student['Stu_Name']); ?><br>
                        - 系所年級：<?php echo htmlspecialchars($student['Stu_Department_Grade']); ?><br>
                        - 電話：<?php echo htmlspecialchars($student['Stu_Phone']); ?><br>
                        - 電子郵件：<?php echo htmlspecialchars($student['Stu_Email']); ?><br>
                    </div>
                <?php } ?>
            </div>

            <div class="info">
                <strong>作品資料：</strong>
                <?php foreach ($team_data['works'] as $work) { ?>
                    <div class="info">
                        - 作品名稱：<?php echo htmlspecialchars($work['Work_Name']); ?><br>
                        - 描述：<?php echo htmlspecialchars($work['Work_Description']); ?><br>
                        - 程式碼：<?php echo htmlspecialchars($work['Work_Code']); ?><br>
                        - 影片：<?php echo htmlspecialchars($work['Work_Video']); ?><br>
                        <img src="<?php echo htmlspecialchars($work['Work_Poster']); ?>" alt="海報圖片">
                    </div>
                <?php } ?>
            </div>

            <div class="info">
                <strong>指導老師：</strong>
                <?php $teacher = $team_data['teacher']; ?>
                - 姓名：<?php echo htmlspecialchars($teacher['Teacher_Name']); ?><br>
                - 任職單位：<?php echo htmlspecialchars($teacher['Teacher_Department']); ?><br>
                - 電話：<?php echo htmlspecialchars($teacher['Teacher_Phone']); ?><br>
                - 電子郵件：<?php echo htmlspecialchars($teacher['Teacher_Email']); ?><br>
            </div>

            <hr> <!-- 隔開不同隊伍的資料 -->
        <?php } ?>

        <!-- 回到主頁按鈕 -->
        <a href="main.php">
            <button class="back-btn">回到主頁</button>
        </a>
    </div>

</body>
</html>

