<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    header("Location: login.html");
    exit();
}
require("connect.php");
mysqli_set_charset($link, "utf8mb4");
$username = $_SESSION["user"];

$msg = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldpass = $_POST["oldpass"] ?? "";
    $newpass = $_POST["newpass"] ?? "";
    $newpass2 = $_POST["newpass2"] ?? "";

    if ($newpass == "" || $newpass != $newpass2) {
        $msg = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน";
    } else {
        // ตรวจสอบรหัสผ่านเดิมจากตาราง user
        $stmt = mysqli_prepare($link, "SELECT 1 FROM user WHERE username=? AND password=?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $oldpass);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) == 0) {
            $msg = "รหัสผ่านเดิมไม่ถูกต้อง";
        } else {
            $up = mysqli_prepare($link, "UPDATE user SET password=? WHERE username=?");
            mysqli_stmt_bind_param($up, "ss", $newpass, $username);
            if (mysqli_stmt_execute($up)) {
                $success = true;
                $msg = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
            } else {
                $msg = "ไม่สามารถเปลี่ยนรหัสผ่านได้: " . mysqli_error($link);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน</title>
    <script>
        function Checkform() {
            var f = document.repass;
            if (f.oldpass.value == "") {
                alert("กรุณากรอกรหัสผ่านเดิม");
                f.oldpass.focus();
                return false;
            }
            if (f.newpass.value == "") {
                alert("กรุณากรอกรหัสผ่านใหม่");
                f.newpass.focus();
                return false;
            }
            if (f.newpass2.value == "") {
                alert("กรุณากรอกยืนยันรหัสผ่านใหม่");
                f.newpass2.focus();
                return false;
            }
            if (f.newpass.value != f.newpass2.value) {
                alert("รหัสผ่านใหม่ไม่ตรงกัน กรุณากรอกใหม่");
                f.newpass2.focus();
                return false;
            }
            return confirm("ยืนยันการเปลี่ยนรหัสผ่าน?");
        }
    </script>
</head>
<body>
    <h2>เปลี่ยนรหัสผ่าน</h2>
    <p>ผู้ใช้งาน : <b><?php echo htmlspecialchars($username); ?></b></p>

    <?php if ($msg != "") { ?>
        <p style="color: <?php echo $success ? 'green' : 'red'; ?>;"><?php echo htmlspecialchars($msg); ?></p>
    <?php } ?>

    <form action="repassword.php" name="repass" method="post" onsubmit="return Checkform();">
        รหัสผ่านเดิม : <input type="password" size="20" name="oldpass" maxlength="20"><br><br>
        รหัสผ่านใหม่ : <input type="password" size="20" name="newpass" maxlength="20"><br><br>
        ยืนยันรหัสผ่านใหม่ : <input type="password" size="20" name="newpass2" maxlength="20"><br><br>
        <input type="submit" value="ยืนยันการเปลี่ยนรหัสผ่าน">
        <input type="reset" value="กรอกใหม่">
        <input type="button" value="กลับหน้าหลัก" onclick="window.location.href='dashboard.php'">
    </form>
</body>
</html>
