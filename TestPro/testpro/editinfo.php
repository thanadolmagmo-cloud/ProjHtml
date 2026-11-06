<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    header("Location: login.html");
    exit();
}
require("connect.php");
mysqli_set_charset($link, "utf8mb4");
$username = $_SESSION["user"];

function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }

// ดึงข้อมูลเดิม
$stmt = mysqli_prepare($link, "SELECT * FROM information WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$row) {            // ยังไม่มีข้อมูล ให้ไปเพิ่มก่อน
    header("Location: infor.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $membername = trim($_POST["membername"]);
    $nickname   = trim($_POST["nickname"]);
    $sex        = $_POST["sex"] == "female" ? "female" : "male";
    $birthdate  = (int)$_POST["birthdate"];
    $birthmonth = (int)$_POST["birthmonth"];
    $birthyear  = trim($_POST["birthyear"]);
    $address    = trim($_POST["address"]);
    $email      = trim($_POST["email"]);
    $mobile     = trim($_POST["mobile"]);
    $fav1 = $_POST["fav1"] ?? "";
    $fav2 = $_POST["fav2"] ?? "";
    $fav3 = $_POST["fav3"] ?? "";

    // เลขบัตรประชาชนไม่ให้แก้ไข
    $up = mysqli_prepare($link,
        "UPDATE information SET membername=?, nickname=?, sex=?, birthdate=?, birthmonth=?, birthyear=?,
         address=?, email=?, mobile=?, fav1=?, fav2=?, fav3=? WHERE username=?");
    mysqli_stmt_bind_param($up, "sssiissssssss",
        $membername, $nickname, $sex, $birthdate, $birthmonth, $birthyear,
        $address, $email, $mobile, $fav1, $fav2, $fav3, $username);
    if (mysqli_stmt_execute($up)) {
        header("Location: viewinfo.php");
        exit();
    }
    $error = "ไม่สามารถบันทึกการแก้ไขได้: " . mysqli_error($link);
    $row = array_merge($row, $_POST);   // คงค่าที่กรอกไว้
    $row["fav1"] = $fav1; $row["fav2"] = $fav2; $row["fav3"] = $fav3;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <h2 style="color: blue; text-align: center;">แก้ไขข้อมูลส่วนตัว</h2>
    <h3 style="color: blue; text-align: center;">ปรับปรุงข้อมูลของคุณ</h3>
    <?php if ($error) echo "<p style='color:red; text-align:center;'>" . h($error) . "</p>"; ?>

    <form action="editinfo.php" method="post" name="editform">
        <table width="70%" class="center">
            <tr>
                <td colspan="2" style="background-color: blue;">
                    <div style="color:white;">&nbsp;แก้ไขข้อมูลส่วนตัวผู้ใช้งาน</div>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; text-align: right;">หมายเลขประจำตัวประชาชน : </td>
                <td style="width: 60%; text-align: left;">
                    <input type="text" size="15" name="idcard" maxlength="13" value="<?php echo h($row['idcard']); ?>" readonly style="background-color: #eef;">
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">ชื่อ - นามสกุล : </td>
                <td><input type="text" size="20" name="membername" value="<?php echo h($row['membername']); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">ชื่อเล่น : </td>
                <td><input type="text" size="10" name="nickname" value="<?php echo h($row['nickname']); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">เพศ :</td>
                <td>
                    <input type="radio" name="sex" value="male" <?php echo $row['sex'] == 'male' ? 'checked' : ''; ?>> <label>ชาย</label>
                    <input type="radio" name="sex" value="female" <?php echo $row['sex'] == 'female' ? 'checked' : ''; ?>> <label>หญิง</label>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">วันเกิด :</td>
                <td>
                    <label>วันที่ : </label>
                    <input type="number" size="3" name="birthdate" min="1" max="31" value="<?php echo h($row['birthdate']); ?>" required>
                    <label>เดือน : </label>
                    <select name="birthmonth">
                        <?php
                        $months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                                   "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
                        foreach ($months as $i => $m) {
                            $sel = ((int)$row['birthmonth'] == $i + 1) ? "selected" : "";
                            echo "<option value='" . ($i + 1) . "' $sel>$m</option>";
                        }
                        ?>
                    </select>
                    <label>พ.ศ. : </label>
                    <input type="text" size="4" name="birthyear" maxlength="4" pattern="\d{4}" value="<?php echo h($row['birthyear']); ?>" required>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">ที่อยู่ : </td>
                <td><textarea rows="5" cols="30" name="address" required><?php echo h($row['address']); ?></textarea></td>
            </tr>
            <tr>
                <td style="text-align: right;">อีเมล์ : </td>
                <td><input type="email" size="20" name="email" value="<?php echo h($row['email']); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">เบอร์โทรศัพท์ : </td>
                <td><input type="text" size="20" name="mobile" value="<?php echo h($row['mobile']); ?>" required></td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: blue;">
                    <div style="color: White">&nbsp;ข้อมูลเพิ่มเติม</div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">สิ่งที่ทำในเวลาว่าง : </td>
                <td>
                    <input type="checkbox" name="fav1" value="อ่านหนังสือ" <?php echo $row['fav1'] != '' ? 'checked' : ''; ?>> อ่านหนังสือ<br>
                    <input type="checkbox" name="fav2" value="เล่นกีฬา" <?php echo $row['fav2'] != '' ? 'checked' : ''; ?>> เล่นกีฬา<br>
                    <input type="checkbox" name="fav3" value="เขียนโปรแกรม" <?php echo $row['fav3'] != '' ? 'checked' : ''; ?>> เขียนโปรแกรม<br>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background-color:aliceblue; text-align: center;">
                    <input type="submit" value="บันทึกการแก้ไข">&ensp;
                    <button type="button" onclick="window.location.href='viewinfo.php';">ยกเลิก</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
