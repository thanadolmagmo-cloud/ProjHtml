<?php
    session_start();
    if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
        header("Location: login.html");
        exit();
    }
    $usertype = $_SESSION["usertype"] ?? "";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <table style="text-align: center;width: 80%;margin-left: auto;margin-right: auto;">
        <tr>
            <td>
                <table style="width: 100%;background-color: #3fffb9;">
                    <tr>
                        <td style="width: 20%;">
                            <img src="nongkhai.png" alt="Logo" style="width: 100px; height: auto;">
                        </td>
                        <td style="width: 80%;">
                            <div style="text-align: right;">
                                <?php echo "ยินดีต้อนรับคุณ " . htmlspecialchars($_SESSION["name"] ?? $_SESSION["user"]); ?>
                                <a href="logout.php" onclick="return confirm('คุณต้องการออกจากระบบ')">ออกจากระบบ</a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table style="width: 100%;background-color: #0efffb;">
                    <tr>
                        <td style="width: 20%;text-align: left; vertical-align: top;">
                        <?php if ($usertype == "admin") { ?>
                            [Menu Admin]<br>
                            <a href="viewinfo.php">ข้อมูลส่วนตัวผู้ดูแลระบบ</a><br>
                            <a href="repassword.php">เปลี่ยนรหัสผ่าน</a><br>
                            จัดการข้อมูลประเภทสถานที่<br>
                            จัดการข้อมูลสถานที่<br>
                            จัดการข้อมูลสินค้า <br>
                        <?php } elseif ($usertype == "member") { ?>
                            [Menu Member]<br>
                            <a href="viewinfo.php">ข้อมูลส่วนตัว</a><br>
                            <a href="repassword.php">เปลี่ยนรหัสผ่าน</a><br>
                        <?php } ?>
                        </td>
                        <td style="width: 80%;text-align: center;background-color: #ffe30e; vertical-align: top;">
                            [main]
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
