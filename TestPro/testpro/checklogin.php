<?php
header("Content-Type: text/html; charset=UTF-8");
require("connect.php");

if ($link) {

    $username = $_POST["user"];
    $password = $_POST["pass"];

    $stmt = mysqli_prepare($link, "select * from user where username=? and password=?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $querysql = mysqli_stmt_get_result($stmt);
    $num = mysqli_num_rows($querysql);

    if ($num == 0) {
        echo "ข้อมูลตรวจสอบสิทธิ์ไม่ถูกต้อง กรุณากรอกใหม่อีกครั้ง";
        echo "<a href='login.html'>ตรวจสอบสิทธิ์เข้าใช้งาน</a> ";
    } else {
        $data = mysqli_fetch_assoc($querysql);

        session_start();
        $_SESSION["user"] = $data["username"];
        $_SESSION["name"] = $data["name"];
        $_SESSION["usertype"] = $data["usertype"];
        header("Location: dashboard.php");
        exit();
    }
}
?>