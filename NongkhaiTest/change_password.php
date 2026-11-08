<?php
require_once 'connect.php';
checkMember();   // ใช้ได้ทั้งสมาชิกและผู้ดูแลระบบ
$nav_active = 'password';
$username = $_SESSION['username'];
$msg = ''; $ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new1 = $_POST['newpass']  ?? '';
    $new2 = $_POST['newpass2'] ?? '';

    if ($new1 === '' || $new1 !== $new2) {
        $msg = 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน';
    } else {
        $u = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
        $u->bind_param("ss", $new1, $username);
        if ($u->execute()) { $ok = true; $msg = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว'; }
        else { $msg = 'ไม่สามารถเปลี่ยนรหัสผ่านได้'; }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container" style="max-width: 480px;">
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">🔑 เปลี่ยนรหัสผ่าน</div>
        <div class="card-body">
            <p class="text-muted">ผู้ใช้งาน: <strong><?= h($username) ?></strong></p>
            <?php if ($msg): ?>
                <div class="alert alert-<?= $ok ? 'success' : 'danger' ?>"><?= h($msg) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">รหัสผ่านใหม่</label>
                    <input type="password" name="newpass" class="form-control" autocomplete="new-password" maxlength="20" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="newpass2" class="form-control" autocomplete="new-password" maxlength="20" required>
                </div>
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('ยืนยันการเปลี่ยนรหัสผ่าน?')">บันทึกรหัสผ่านใหม่</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>