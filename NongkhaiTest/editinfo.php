<?php
require_once 'connect.php';
checkMember();
// admin ไม่มีข้อมูลส่วนตัวในตาราง information
if (($_SESSION['usertype'] ?? '') === 'admin') {
    header("Location: index.php");
    exit();
}
$nav_active = 'info';
$username = $_SESSION['username'];
$error = '';

// โหลดข้อมูลปัจจุบัน
$stmt = $conn->prepare("SELECT * FROM information WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();

if ($info && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $membername = trim($_POST['membername'] ?? '');
    $nickname   = trim($_POST['nickname'] ?? '');
    $sex        = ($_POST['sex'] ?? '') === 'female' ? 'female' : 'male';
    $birthdate  = (int)($_POST['birthdate'] ?? 0);
    $birthmonth = (int)($_POST['birthmonth'] ?? 0);
    $birthyear  = trim($_POST['birthyear'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $fav1       = trim($_POST['fav1'] ?? '');
    $fav2       = trim($_POST['fav2'] ?? '');
    $fav3       = trim($_POST['fav3'] ?? '');

    if ($membername === '' || $nickname === '') {
        $error = "กรุณากรอกชื่อ-นามสกุล และชื่อเล่น";
    } elseif ($birthdate < 1 || $birthdate > 31 || $birthmonth < 1 || $birthmonth > 12 || !preg_match('/^\d{4}$/', $birthyear)) {
        $error = "วัน/เดือน/ปีเกิดไม่ถูกต้อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    } elseif ($mobile === '' || $address === '') {
        $error = "กรุณากรอกเบอร์โทรศัพท์และที่อยู่";
    } else {
        $conn->begin_transaction();
        try {
            $s1 = $conn->prepare("UPDATE information SET membername=?, nickname=?, sex=?, birthdate=?, birthmonth=?, birthyear=?, address=?, email=?, mobile=?, fav1=?, fav2=?, fav3=? WHERE username=?");
            $s1->bind_param("sssiissssssss", $membername, $nickname, $sex, $birthdate, $birthmonth, $birthyear, $address, $email, $mobile, $fav1, $fav2, $fav3, $username);
            $s1->execute();

            // ให้ชื่อในตาราง user (ที่แสดงบน navbar) ตรงกันด้วย
            $s2 = $conn->prepare("UPDATE user SET name=? WHERE username=?");
            $s2->bind_param("ss", $membername, $username);
            $s2->execute();

            $conn->commit();
            $_SESSION['name'] = $membername;
            header("Location: viewinfo.php?updated=1");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง";
        }
    }
    // ถ้ามีข้อผิดพลาด ให้แสดงค่าที่ผู้ใช้เพิ่งกรอกกลับในฟอร์ม
    $info = array_merge($info, [
        'membername' => $membername, 'nickname' => $nickname, 'sex' => $sex,
        'birthdate' => $birthdate, 'birthmonth' => $birthmonth, 'birthyear' => $birthyear,
        'address' => $address, 'email' => $email, 'mobile' => $mobile,
        'fav1' => $fav1, 'fav2' => $fav2, 'fav3' => $fav3,
    ]);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลส่วนตัว - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5" style="max-width: 750px;">
<?php if (!$info): ?>
    <div class="alert alert-danger">ไม่พบข้อมูลสมาชิกของบัญชีผู้ใช้นี้</div>
<?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning"><h5 class="m-0">✏️ แก้ไขข้อมูลส่วนตัว</h5></div>
        <div class="card-body p-4">
            <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

            <form method="POST">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= h($username) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เลขบัตรประชาชน <small class="text-muted">(แก้ไขไม่ได้)</small></label>
                        <input type="text" class="form-control" value="<?= h($info['idcard']) ?>" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" name="membername" class="form-control" maxlength="100" value="<?= h($info['membername']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ชื่อเล่น</label>
                        <input type="text" name="nickname" class="form-control" maxlength="50" value="<?= h($info['nickname']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เพศ</label>
                        <select name="sex" class="form-select" required>
                            <option value="male" <?= $info['sex'] === 'male' ? 'selected' : '' ?>>ชาย</option>
                            <option value="female" <?= $info['sex'] === 'female' ? 'selected' : '' ?>>หญิง</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">วันเกิด (1-31)</label>
                        <input type="number" name="birthdate" class="form-control" min="1" max="31" value="<?= h($info['birthdate']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เดือนเกิด (1-12)</label>
                        <input type="number" name="birthmonth" class="form-control" min="1" max="12" value="<?= h($info['birthmonth']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">พ.ศ. เกิด</label>
                        <input type="text" name="birthyear" class="form-control" maxlength="4" pattern="[0-9]{4}" value="<?= h($info['birthyear']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" value="<?= h($info['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" name="mobile" class="form-control" maxlength="20" value="<?= h($info['mobile']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ที่อยู่</label>
                        <textarea name="address" class="form-control" rows="2" required><?= h($info['address']) ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">งานอดิเรก / สิ่งที่ชอบ 1</label>
                        <input type="text" name="fav1" class="form-control" value="<?= h($info['fav1'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">สิ่งที่ชอบ 2</label>
                        <input type="text" name="fav2" class="form-control" value="<?= h($info['fav2'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">สิ่งที่ชอบ 3</label>
                        <input type="text" name="fav3" class="form-control" value="<?= h($info['fav3'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning flex-fill" onclick="return confirm('ยืนยันการบันทึกการแก้ไข?')">บันทึกการแก้ไข</button>
                    <a href="viewinfo.php" class="btn btn-outline-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>