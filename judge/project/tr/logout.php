<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // 轉到登入頁面
exit();
?>