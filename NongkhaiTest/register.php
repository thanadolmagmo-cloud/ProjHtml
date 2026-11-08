<?php
require_once 'connect.php';
$error = '';
function old($k) { return h($_POST[$k] ?? ''); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $idcard     = trim($_POST['idcard'] ?? '');
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

    if (!preg_match('/^\d{13}$/', $idcard)) {
        $error = "เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก";
    } elseif ($username === '' || $password === '') {
        $error = "กรุณากรอก Username และ Password";
    } else {
        $check = $conn->prepare("SELECT username FROM user WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว!";
        } else {
            $conn->begin_transaction();
            try {
                $usertype = 'member';
                $s1 = $conn->prepare("INSERT INTO user (username, password, name, usertype) VALUES (?, ?, ?, ?)");
                $s1->bind_param("ssss", $username, $password, $membername, $usertype);
                $s1->execute();

                $s2 = $conn->prepare("INSERT INTO information (username, idcard, membername, nickname, sex, birthdate, birthmonth, birthyear, address, email, mobile, fav1) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $s2->bind_param("sssssiisssss", $username, $idcard, $membername, $nickname, $sex, $birthdate, $birthmonth, $birthyear, $address, $email, $mobile, $fav1);
                $s2->execute();

                $conn->commit();
                header("Location: login.php?registered=1");
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "เกิดข้อผิดพลาด: เลขบัตรประชาชนนี้อาจถูกใช้สมัครไปแล้ว";
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
    <title>สมัครสมาชิก - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 650px;">
    <div class="card shadow">
        <div class="card-header bg-success text-white"><h4 class="m-0 text-center">สมัครสมาชิกใหม่</h4></div>
        <div class="card-body p-4">
            <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

            <form method="POST">
                <h5 class="text-success mb-3">1. ข้อมูลเข้าสู่ระบบ</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Username (ชื่อผู้ใช้)</label>
                        <input type="text" name="username" class="form-control" maxlength="20" value="<?= old('username') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password (รหัสผ่าน)</label>
                        <input type="password" name="password" class="form-control" maxlength="20" required>
                    </div>
                </div>

                <h5 class="text-success mb-3">2. ข้อมูลส่วนตัว</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" name="membername" class="form-control" maxlength="100" value="<?= old('membername') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ชื่อเล่น</label>
                        <input type="text" name="nickname" class="form-control" maxlength="50" value="<?= old('nickname') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เลขบัตรประชาชน (13 หลัก)</label>
                        <input type="text" name="idcard" class="form-control" maxlength="13" pattern="[0-9]{13}" value="<?= old('idcard') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เพศ</label>
                        <select name="sex" class="form-select" required>
                            <option value="male">ชาย</option>
                            <option value="female" <?= ($_POST['sex'] ?? '') === 'female' ? 'selected' : '' ?>>หญิง</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">วันเกิด (1-31)</label>
                        <input type="number" name="birthdate" class="form-control" min="1" max="31" value="<?= old('birthdate') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เดือนเกิด (1-12)</label>
                        <input type="number" name="birthmonth" class="form-control" min="1" max="12" value="<?= old('birthmonth') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">พ.ศ. เกิด (เช่น 2545)</label>
                        <input type="text" name="birthyear" class="form-control" maxlength="4" pattern="[0-9]{4}" value="<?= old('birthyear') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" name="mobile" class="form-control" maxlength="20" value="<?= old('mobile') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">ที่อยู่</label>
                        <textarea name="address" class="form-control" rows="2" required><?= old('address') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">งานอดิเรก / สิ่งที่ชอบ</label>
                        <input type="text" name="fav1" class="form-control" placeholder="เช่น อ่านหนังสือ, ท่องเที่ยว" value="<?= old('fav1') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2">ยืนยันการสมัครสมาชิก</button>
                <div class="mt-3 text-center">
                    <a href="login.php" class="text-secondary">มีบัญชีผู้ใช้แล้ว? เข้าสู่ระบบ</a> |
                    <a href="index.php" class="text-secondary">กลับหน้าแรก</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>