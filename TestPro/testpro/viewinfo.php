<?php
session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    header("Location: login.html");
    exit();
}
require("connect.php");
mysqli_set_charset($link, "utf8mb4");

$stmt = mysqli_prepare($link, "SELECT * FROM information WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $_SESSION["user"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }

$months = ["", "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
           "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ดูข้อมูลส่วนตัว</title>
    <style>
        body { font-family: "Sarabun", "Tahoma", sans-serif; background-color: #f9f9f9; padding: 20px; }
        .form-container { width: 80%; margin: 0 auto; background-color: #fff; padding: 20px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        h3.section-title { margin-top: 15px; margin-bottom: 5px; font-size: 16px; font-weight: bold; }
        table.form-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.form-table td { border: 1px solid #000; padding: 6px 8px; font-size: 14px; vertical-align: middle; }
        .label-bg { background-color: #e6e6e6; font-weight: bold; }
        .no-data-box { text-align: center; padding: 30px; border: 1px dashed #ccc; margin-top: 20px; }
        .btn-add, .btn-edit, .btn-back { padding: 8px 16px; font-size: 15px; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-add { background-color: #4CAF50; }
        .btn-add:hover { background-color: #45a049; }
        .btn-edit { background-color: #ff9800; }
        .btn-edit:hover { background-color: #e68a00; }
        .btn-back { background-color: #777; }
    </style>
</head>
<body>

<div class="form-container">
    <h2 style="text-align: center; color: blue;">ข้อมูลส่วนตัวผู้ใช้งาน</h2>

<?php if (!$row) { ?>
    <!-- ยังไม่มีข้อมูล -->
    <div class="no-data-box">
        <p style="font-size: 18px; color: red; margin-bottom: 15px;">ยังไม่มีข้อมูลส่วนตัวในระบบ</p>
        <button class="btn-add" onclick="window.location.href='infor.php';">+ เพิ่มข้อมูลส่วนตัว</button>
    </div>
<?php } else {
    $hobbies = array_filter([$row["fav1"], $row["fav2"], $row["fav3"]]);
    $month = $months[(int)$row["birthmonth"]] ?? "";
?>
    <!-- มีข้อมูลแล้ว -->
    <h3 class="section-title">๑. ข้อมูลส่วนตัว</h3>
    <table class="form-table">
        <tr>
            <td class="label-bg" style="width: 20%;">หมายเลขบัตรประชาชน</td>
            <td style="width: 30%;"><?php echo h($row["idcard"]); ?></td>
            <td class="label-bg" style="width: 15%;">เพศ</td>
            <td style="width: 35%;"><?php echo $row["sex"] == "male" ? "ชาย" : "หญิง"; ?></td>
        </tr>
        <tr>
            <td class="label-bg">ชื่อ - นามสกุล</td>
            <td><?php echo h($row["membername"]); ?></td>
            <td class="label-bg">ชื่อเล่น</td>
            <td><?php echo h($row["nickname"]); ?></td>
        </tr>
        <tr>
            <td class="label-bg">วัน / เดือน / ปีเกิด (พ.ศ.)</td>
            <td colspan="3"><?php echo h($row["birthdate"]) . " / " . $month . " / " . h($row["birthyear"]); ?></td>
        </tr>
    </table>

    <h3 class="section-title">๒. ข้อมูลการติดต่อ</h3>
    <table class="form-table">
        <tr>
            <td class="label-bg" style="width: 20%;">ที่อยู่</td>
            <td colspan="3"><?php echo nl2br(h($row["address"])); ?></td>
        </tr>
        <tr>
            <td class="label-bg" style="width: 20%;">อีเมล</td>
            <td style="width: 30%;"><?php echo h($row["email"]); ?></td>
            <td class="label-bg" style="width: 15%;">โทรศัพท์มือถือ</td>
            <td style="width: 35%;"><?php echo h($row["mobile"]); ?></td>
        </tr>
    </table>

    <h3 class="section-title">๓. ข้อมูลเพิ่มเติม</h3>
    <table class="form-table">
        <tr>
            <td class="label-bg" style="width: 20%;">สิ่งที่ทำในเวลาว่าง</td>
            <td><?php echo $hobbies ? h(implode(", ", $hobbies)) : "-"; ?></td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 20px;">
        <button class="btn-edit" onclick="window.location.href='editinfo.php';">แก้ไขข้อมูล</button>
    </div>
<?php } ?>

    <div style="text-align: center; margin-top: 15px;">
        <button class="btn-back" onclick="window.location.href='dashboard.php';">กลับหน้าหลัก</button>
    </div>
</div>

</body>
</html>
