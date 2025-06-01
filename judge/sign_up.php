<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('config.php'); // 引入資料庫連接設定檔

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 收集隊伍資料
    $team_name = $_POST['team_name'];
    $team_password = $_POST['team_password'];

    // 收集老師資料
    $teacher_name = $_POST['teacher_name'];
    $teacher_password = $_POST['teacher_password']; // 新增老師密碼
    $teacher_department = $_POST['teacher_department'];
    $teacher_phone = $_POST['teacher_phone'];
    $teacher_email = $_POST['teacher_email'];

    // 收集作品資料
    $work_name = $_POST['work_name'];
    $work_description = $_POST['work_description'];
    $work_code = $_POST['work_code'];
    $work_video = $_POST['work_video'];
    $work_poster = $_POST['work_poster'];  // 直接使用海報的網址

    // 生成新的隊伍 ID
    $sql = "SELECT MAX(CAST(SUBSTRING(Team_ID, 2) AS UNSIGNED)) AS max_id FROM team";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $new_id_number = $row['max_id'] + 1;
    $new_team_id = "t" . str_pad($new_id_number, 5, "0", STR_PAD_LEFT);

    // 插入隊伍資料
    $sql_team = "INSERT INTO team (Team_ID, Team_Name, Team_Password) VALUES ('$new_team_id', '$team_name', '$team_password')";
    if (!$conn->query($sql_team)) {
        echo "隊伍資料插入失敗：" . $conn->error . "<br>";
        exit;
    }

    // 生成新的老師 ID
    $sql_teacher_id = "SELECT MAX(CAST(SUBSTRING(Teacher_ID, 2) AS UNSIGNED)) AS max_teacher_id FROM teacher";
    $result_teacher_id = $conn->query($sql_teacher_id);
    $row_teacher_id = $result_teacher_id->fetch_assoc();
    $new_teacher_id_number = $row_teacher_id['max_teacher_id'] + 1;
    $new_teacher_id = "t" . str_pad($new_teacher_id_number, 5, "0", STR_PAD_LEFT);

    // 插入老師資料
    $sql_teacher = "INSERT INTO teacher (Teacher_ID, Teacher_Name, Teacher_Password, Teacher_Department, Teacher_Phone, Teacher_Email, Team_ID) 
                    VALUES ('$new_teacher_id', '$teacher_name', '$teacher_password', '$teacher_department', '$teacher_phone', '$teacher_email', '$new_team_id')";
    if (!$conn->query($sql_teacher)) {
        echo "老師資料插入失敗：" . $conn->error . "<br>";
        exit;
    }

    // 處理多位學生資料
    $student_ids = $_POST['student_id'];
    $student_names = $_POST['student_name'];
    $department_grades = $_POST['department_grade'];
    $phones = $_POST['phone'];
    $emails = $_POST['email'];

    foreach ($student_ids as $key => $student_id) {
        $student_name = $student_names[$key];
        $department_grade = $department_grades[$key];
        $phone = $phones[$key];
        $email = $emails[$key];

        $sql_student = "INSERT INTO student (Stu_ID, Stu_Name, Stu_Department_Grade, Stu_Phone, Stu_Email, Team_ID) 
                        VALUES ('$student_id', '$student_name', '$department_grade', '$phone', '$email', '$new_team_id')";
        if (!$conn->query($sql_student)) {
            echo "學生資料插入失敗：" . $conn->error . "<br>";
            exit;
        }
    }

    // 插入作品資料
    $sql_work = "SELECT MAX(CAST(SUBSTRING(Work_ID, 2) AS UNSIGNED)) AS max_work_id FROM works";
    $result_work = $conn->query($sql_work);
    $row_work = $result_work->fetch_assoc();
    $new_work_id_number = $row_work['max_work_id'] + 1;
    $new_work_id = "w" . str_pad($new_work_id_number, "5", "0", STR_PAD_LEFT);

    $sql_works = "INSERT INTO works (Work_ID, Work_Name, Work_Description, Work_Code, Work_Video, Work_Poster, Team_ID)
                  VALUES ('$new_work_id', '$work_name', '$work_description', '$work_code', '$work_video', '$work_poster', '$new_team_id')";
    if (!$conn->query($sql_works)) {
        echo "作品資料插入失敗：" . $conn->error . "<br>";
        exit;
    }

    echo "報名完成！<br>";
    echo "<a href='sign_up.php'>返回報名頁面</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隊伍報名</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="email"], input[type="password"], textarea {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        input[type="file"] {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 20px;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        a {
            display: inline-block;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            color: #4CAF50;
            font-size: 16px;
        }
        a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .student-group {
            margin-top: 20px;
        }
        .remove-student {
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>隊伍報名表單</h2>
        <form action="sign_up.php" method="POST" enctype="multipart/form-data">
            <!-- 隊伍資料 -->
            <div class="form-group">
                <label for="team_name">隊伍名稱:</label>
                <input type="text" name="team_name" required>
            </div>
            <div class="form-group">
                <label for="team_password">隊伍密碼:</label>
                <input type="password" name="team_password" required>
            </div>

            <!-- 老師資料 -->
            <div class="form-group">
                <label for="teacher_name">指導老師姓名:</label>
                <input type="text" name="teacher_name" required>
            </div>
            <div class="form-group">
                <label for="teacher_password">老師密碼:</label>
                <input type="password" name="teacher_password" required>
            </div>
            <div class="form-group">
                <label for="teacher_department">任職單位:</label>
                <input type="text" name="teacher_department" required>
            </div>
            <div class="form-group">
                <label for="teacher_phone">聯絡電話:</label>
                <input type="text" name="teacher_phone" required>
            </div>
            <div class="form-group">
                <label for="teacher_email">Email:</label>
                <input type="email" name="teacher_email" required>
            </div>

            <!-- 學生資料 -->
            <div id="students" class="student-group">
                <div class="student">
                    <label for="student_id[]">學生 ID:</label>
                    <input type="text" name="student_id[]" required>
                    <label for="student_name[]">學生姓名:</label>
                    <input type="text" name="student_name[]" required>
                    <label for="department_grade[]">科系及年級:</label>
                    <input type="text" name="department_grade[]" required>
                    <label for="phone[]">電話:</label>
                    <input type="text" name="phone[]" required>
                    <label for="email[]">電子郵件:</label>
                    <input type="email" name="email[]" required>
                </div>
            </div>
            <button type="button" onclick="addStudent()">新增學生</button>

            <!-- 作品資料 -->
            <div class="form-group">
                <label for="work_name">作品名稱:</label>
                <input type="text" name="work_name" required>
            </div>
            <div class="form-group">
                <label for="work_description">作品描述:</label>
                <textarea name="work_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="work_code">程式碼連結:</label>
                <input type="text" name="work_code">
            </div>
            <div class="form-group">
                <label for="work_video">影片連結:</label>
                <input type="text" name="work_video">
            </div>
            <div class="form-group">
                <label for="work_poster">海報網址:</label>
                <input type="text" name="work_poster" placeholder="請輸入海報的網址" required>
            </div>

            <!-- 提交按鈕 -->
            <input type="submit" value="報名">
        </form>
    </div>

    <script>
        function addStudent() {
            const studentGroup = document.createElement('div');
            studentGroup.classList.add('student');

            studentGroup.innerHTML = `
                <label for="student_id[]">學生 ID:</label>
                <input type="text" name="student_id[]" required>
                <label for="student_name[]">學生姓名:</label>
                <input type="text" name="student_name[]" required>
                <label for="department_grade[]">科系及年級:</label>
                <input type="text" name="department_grade[]" required>
                <label for="phone[]">電話:</label>
                <input type="text" name="phone[]" required>
                <label for="email[]">電子郵件:</label>
                <input type="email" name="email[]" required>
            `;
            document.getElementById('students').appendChild(studentGroup);
        }
    </script>
</body>
</html>






