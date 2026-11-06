<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    header("Location: login.html");
    exit();
}
require("connect.php");
mysqli_set_charset($link, "utf8mb4");
$username = $_SESSION["user"];

// ถ้ามีข้อมูลอยู่แล้ว ให้ไปหน้าแก้ไขแทน
$chk = mysqli_prepare($link, "SELECT 1 FROM information WHERE username=?");
mysqli_stmt_bind_param($chk, "s", $username);
mysqli_stmt_execute($chk);
if (mysqli_num_rows(mysqli_stmt_get_result($chk)) > 0) {
    header("Location: viewinfo.php");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idcard     = trim($_POST["idcard"]);
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

    if (!preg_match('/^\d{13}$/', $idcard)) {
        $error = "หมายเลขประจำตัวประชาชนต้องเป็นตัวเลข 13 หลัก";
    } else {
        $stmt = mysqli_prepare($link,
            "INSERT INTO information (username, idcard, membername, nickname, sex, birthdate, birthmonth, birthyear, address, email, mobile, fav1, fav2, fav3)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssssiisssssss",
            $username, $idcard, $membername, $nickname, $sex, $birthdate, $birthmonth,
            $birthyear, $address, $email, $mobile, $fav1, $fav2, $fav3);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: viewinfo.php");
            exit();
        }
        $error = "ไม่สามารถบันทึกข้อมูลได้: " . mysqli_error($link);
    }
}

function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }
function old($k) { return h($_POST[$k] ?? ""); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลส่วนตัว</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <h2 style="color: blue; text-align: center;">เพิ่มข้อมูลส่วนตัว</h2>
    <h3 style="color: blue; text-align: center;">กรุณาป้อนข้อมูลต่อไปนี้</h3>
    <?php if ($error) echo "<p style='color:red; text-align:center;'>" . h($error) . "</p>"; ?>

    <form action="infor.php" method="post" name="registerform">
        <table width="70%" class="center">
            <tr>
                <td colspan="2" style="background-color: blue;">
                    <div style="color:white;">&nbsp;ข้อมูลส่วนตัวผู้ใช้งาน</div>
                </td>
            </tr>
            <tr>
                <td style="width: 40%; text-align: right;">หมายเลขประจำตัวประชาชน : </td>
                <td style="width: 60%; text-align: left;">
                    <input type="text" size="15" name="idcard" maxlength="13" pattern="\d{13}" title="ตัวเลข 13 หลัก" value="<?php echo old('idcard'); ?>" required>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">ชื่อ - นามสกุล : </td>
                <td><input type="text" size="20" name="membername" value="<?php echo old('membername'); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">ชื่อเล่น : </td>
                <td><input type="text" size="10" name="nickname" value="<?php echo old('nickname'); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">เพศ :</td>
                <td>
                    <input type="radio" name="sex" value="male" <?php echo (($_POST['sex'] ?? 'male') == 'male') ? 'checked' : ''; ?>> <label>ชาย</label>
                    <input type="radio" name="sex" value="female" <?php echo (($_POST['sex'] ?? '') == 'female') ? 'checked' : ''; ?>> <label>หญิง</label>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">วันเกิด :</td>
                <td>
                    <label>วันที่ : </label>
                    <input type="number" size="3" name="birthdate" min="1" max="31" value="<?php echo old('birthdate'); ?>" required>
                    <label>เดือน : </label>
                    <select name="birthmonth">
                        <?php
                        $months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                                   "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
                        foreach ($months as $i => $m) {
                            $sel = (($_POST['birthmonth'] ?? 1) == $i + 1) ? "selected" : "";
                            echo "<option value='" . ($i + 1) . "' $sel>$m</option>";
                        }
                        ?>
                    </select>
                    <label>พ.ศ. : </label>
                    <input type="text" size="4" name="birthyear" maxlength="4" pattern="\d{4}" value="<?php echo old('birthyear'); ?>" required>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">ที่อยู่ : </td>
                <td><textarea rows="5" cols="30" name="address" required><?php echo old('address'); ?></textarea></td>
            </tr>
            <tr>
                <td style="text-align: right;">อีเมล์ : </td>
                <td><input type="email" size="20" name="email" value="<?php echo old('email'); ?>" required></td>
            </tr>
            <tr>
                <td style="text-align: right;">เบอร์โทรศัพท์ : </td>
                <td><input type="text" size="20" name="mobile" value="<?php echo old('mobile'); ?>" required></td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: blue;">
                    <div style="color: White">&nbsp;ข้อมูลเพิ่มเติม</div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right;">สิ่งที่ทำในเวลาว่าง : </td>
                <td>
                    <input type="checkbox" name="fav1" value="อ่านหนังสือ" <?php echo isset($_POST['fav1']) ? 'checked' : ''; ?>> อ่านหนังสือ<br>
                    <input type="checkbox" name="fav2" value="เล่นกีฬา" <?php echo isset($_POST['fav2']) ? 'checked' : ''; ?>> เล่นกีฬา<br>
                    <input type="checkbox" name="fav3" value="เขียนโปรแกรม" <?php echo isset($_POST['fav3']) ? 'checked' : ''; ?>> เขียนโปรแกรม<br>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="background-color:aliceblue; text-align: center;">
                    <input type="submit" value="บันทึกข้อมูล">&ensp;
                    <input type="reset" value="กรอกใหม่">&ensp;
                    <button type="button" onclick="window.location.href='viewinfo.php';">ยกเลิก</button>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
