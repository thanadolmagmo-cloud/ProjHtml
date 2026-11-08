<?php
require_once 'connect.php';
checkMember();
$nav_active = 'info';
$username = $_SESSION['username'];

$stmt = $conn->prepare("
    SELECT u.username, u.name AS user_name, u.usertype,
           i.idcard, i.membername, i.nickname, i.sex, i.birthdate, i.birthmonth, i.birthyear,
           i.address, i.email, i.mobile, i.fav1, i.fav2, i.fav3
    FROM user u LEFT JOIN information i ON u.username = i.username
    WHERE u.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลส่วนตัว - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">แก้ไขข้อมูลส่วนตัวเรียบร้อยแล้ว</div>
<?php endif; ?>
<?php if ($info): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-success text-white"><h5 class="m-0">👤 ข้อมูลส่วนตัวสมาชิก</h5></div>
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Username:</strong> <?= h($info['username']) ?></div>
                <div class="col-md-5"><strong>ชื่อ-นามสกุล:</strong> <?= h(!empty($info['membername']) ? $info['membername'] : $info['user_name']) ?></div>
                <div class="col-md-3"><strong>ชื่อเล่น:</strong> <?= h($info['nickname'] ?? '-') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>เลขบัตรประชาชน:</strong> <?= h($info['idcard'] ?? '-') ?></div>
                <div class="col-md-5"><strong>เพศ:</strong> <?= isset($info['sex']) ? ($info['sex'] === 'male' ? 'ชาย' : 'หญิง') : '-' ?></div>
                <div class="col-md-3"><strong>วัน/เดือน/ปีเกิด:</strong> <?= !empty($info['birthdate']) ? h($info['birthdate'] . '/' . $info['birthmonth'] . '/' . $info['birthyear']) : '-' ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6"><strong>อีเมล:</strong> <?= h($info['email'] ?? '-') ?></div>
                <div class="col-md-6"><strong>เบอร์โทรศัพท์:</strong> <?= h($info['mobile'] ?? '-') ?></div>
            </div>
            <div class="row mb-3">
                <div class="col-12"><strong>ที่อยู่:</strong> <?= h($info['address'] ?? '-') ?></div>
            </div>
            <div class="row">
                <div class="col-12"><strong>สิ่งที่ชอบ / งานอดิเรก:</strong>
                    <?php
                    $has = false;
                    for ($i = 1; $i <= 3; $i++) {
                        $f = $info["fav$i"] ?? '';
                        if (!empty($f)) { $has = true; echo '<span class="badge bg-secondary me-1">' . h($f) . '</span>'; }
                    }
                    if (!$has) echo '<span class="text-muted">-</span>';
                    ?>
                </div>
            </div>
            <hr>
            <a href="editinfo.php" class="btn btn-warning btn-sm me-1">✏️ แก้ไขข้อมูลส่วนตัว</a>
            <a href="change_password.php" class="btn btn-outline-success btn-sm">🔑 เปลี่ยนรหัสผ่าน</a>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">ไม่พบข้อมูลสมาชิกของบัญชีผู้ใช้นี้</div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>