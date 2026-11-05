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
                            [Logo]
                        </td>
                        <td style="width: 80%;">
                            <div style="text-align: right;">
                                <?php echo "ยินดีต้อนรับคุณ " . htmlspecialchars($_SESSION["user"]); ?>
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
                        <td style="width: 20%;text-align: left;">
                        <?php if ($usertype == "admin") { ?>
                            [Menu Admin]<br>
                            แก้ไขข้อมูลส่วนตัวผู้ดูแลระบบ<br>
                            เปลี่ยนรหัสผ่าน<br>
                            จัดการข้อมูลประเภทสถานที่<br>
                            จัดการข้อมูลสถานที่<br>
                            จัดการข้อมูลสินค้า
                        <?php } elseif ($usertype == "member") { ?>
                            [Menu Member]<br>
                            แก้ไขข้อมูลส่วนตัว<br>
                            เปลี่ยนรหัสผ่าน<br>
                        <?php } ?>
                        </td>
                        <td style="width: 80%;text-align: center;background-color: #ffe30e;">
                            [main]
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>