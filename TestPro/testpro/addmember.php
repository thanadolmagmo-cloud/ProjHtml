<?php
require("connect.php");

if ($link) {
    // รับค่าจากแบบฟอร์ม
    $name = $_POST["name"];
    $username = $_POST["user"];
    $password = $_POST["pass"];

    // 1. ตรวจสอบว่ามี username นี้อยู่แล้วหรือไม่ (ปรับชื่อตาราง user ให้ตรงกับใน phpMyAdmin)
    $sqlcheck = "SELECT * FROM user WHERE username='$username'";
    $querycheck = mysqli_query($link, $sqlcheck);

    if ($querycheck && mysqli_num_rows($querycheck) != 0) {
        echo "Username นี้มีผู้ใช้งานแล้ว ไม่สามารถเพิ่มข้อมูลได้";
    } else {
        // 2. เพิ่มข้อมูลเข้าตาราง (กำหนดค่า usertype เริ่มต้นเป็น 'member')
        $sqladd = "INSERT INTO user (username, password, name, usertype) 
                   VALUES ('$username', '$password', '$name', 'member')";
        
        $queryadd = mysqli_query($link, $sqladd);

        if (!$queryadd) {
            echo "ไม่สามารถเพิ่มข้อมูลได้: " . mysqli_error($link);
        } else {
            echo "ระบบทำการเพิ่มข้อมูลเสร็จแล้ว กรุณาเข้าระบบด้วยการตรวจสอบสิทธิ์<br>";
            echo "<a href='login.html'>ตรวจสอบสิทธิ์เข้าใช้งาน</a>";
        }
    }
}
?>