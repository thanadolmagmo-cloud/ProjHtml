<?php
require_once 'connect.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($u = $res->fetch_assoc()) {
        session_regenerate_id(true);
        $_SESSION['username'] = $u['username'];
        $_SESSION['name']     = $u['name'];
        $_SESSION['usertype'] = $u['usertype'];
        header("Location: index.php");
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    /* แสดงเป็นจุดเหมือนช่องรหัสผ่าน แต่เป็น input แบบ text เพื่อไม่ให้ Chrome จับเป็นฟอร์มรหัสผ่าน */
    .pw-mask { -webkit-text-security: disc; }
</style>
    <link href="theme.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center vh-100">
<div class="container" style="max-width: 400px;">
    <div class="card shadow">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4 text-success">เข้าสู่ระบบ</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ</div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus autocomplete="off" placeholder="ชื่อผู้ใช้งาน">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="text" name="password" class="form-control pw-mask" required autocomplete="off" autocapitalize="off" spellcheck="false" placeholder="รหัสผ่าน">
                </div>
                <button type="submit" class="btn btn-success w-100">Login</button>
            </form>

            <div class="mt-4 pt-3 border-top text-center">
                <p class="mb-1 text-muted">ยังไม่มีบัญชีผู้ใช้?</p>
                <a href="register.php" class="btn btn-outline-success btn-sm w-100 mb-2">สมัครสมาชิกใหม่</a>
                <a href="index.php" class="text-secondary small">กลับหน้าแรก</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>