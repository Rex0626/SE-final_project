<?php
// 啟動 session
session_start();

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

// 查詢資料庫，計算每個隊伍的總分並排序
$sql = "
SELECT 
    t.Team_ID,
    t.Team_Name,
    w.Work_Name,
    w.Work_Description,
    w.Work_Video,
    w.Work_Code,
    w.Work_Poster,
    SUM(s.Score) AS Total_Score
FROM 
    scores s
JOIN 
    team t ON s.Team_ID = t.Team_ID
JOIN 
    works w ON s.Team_ID = w.Team_ID
GROUP BY 
    s.Team_ID, t.Team_Name, w.Work_Name, w.Work_Description, w.Work_Video, w.Work_Code, w.Work_Poster
ORDER BY 
    Total_Score DESC;
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $rank = 1;
    echo "<table border='1' style='width:100%; border-collapse: collapse;'>";
    echo "<tr><th>名次</th><th>隊伍編號</th><th>隊伍名稱</th><th>作品名稱</th><th>作品描述</th><th>影片</th><th>程式碼</th><th>海報</th><th>總分</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $rank++ . "</td>";
        echo "<td>" . htmlspecialchars($row['Team_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Team_Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Work_Name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Work_Description']) . "</td>";
        echo "<td><a href='" . htmlspecialchars($row['Work_Video']) . "' target='_blank'>觀看影片</a></td>";
        echo "<td><a href='" . htmlspecialchars($row['Work_Code']) . "' target='_blank'>程式碼下載</a></td>";
        echo "<td><img src='" . htmlspecialchars($row['Work_Poster']) . "' alt='海報' style='max-width:100px; max-height:100px;'></td>";
        echo "<td>" . htmlspecialchars($row['Total_Score']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "沒有找到任何資料。";
}

// 關閉連線
$conn->close();
?>

<!-- 返回主頁按鈕 -->
<a href="main.php">
    <button class="back-btn">回到主頁</button>
</a>

<style>
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
</style>

