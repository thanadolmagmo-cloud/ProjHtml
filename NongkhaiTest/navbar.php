<?php
// แถบเมนูกลาง  ก่อน include ให้กำหนด $nav_active = 'home' | 'info' | 'places' | 'products' | 'password' | 'admin'
$nav_active = $nav_active ?? '';
$is_login = isset($_SESSION['username']);
$is_admin = $is_login && ($_SESSION['usertype'] ?? '') === 'admin';
function nav_cls($key, $cur) { return 'nav-link' . ($key === $cur ? ' active' : ''); }
?>
<link href="theme.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-dark bg-success navbar-gold mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">🐉 หนองคาย</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="<?= nav_cls('home', $nav_active) ?>" href="index.php">หน้าแรก</a></li>
                <?php if ($is_login): ?>
                    <li class="nav-item"><a class="<?= nav_cls('places', $nav_active) ?>" href="places.php">สถานที่</a></li>
                    <li class="nav-item"><a class="<?= nav_cls('products', $nav_active) ?>" href="products.php">สินค้าชุมชน</a></li>
                    <?php if (!$is_admin): ?>
                        <li class="nav-item"><a class="<?= nav_cls('info', $nav_active) ?>" href="viewinfo.php">ข้อมูลส่วนตัว</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="<?= nav_cls('password', $nav_active) ?>" href="change_password.php">เปลี่ยนรหัสผ่าน</a></li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">จัดการระบบ (Admin)</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="admin_place_types.php">จัดการประเภทสถานที่</a></li>
                                <li><a class="dropdown-item" href="admin_places.php">จัดการสถานที่</a></li>
                                <li><a class="dropdown-item" href="admin_products.php">จัดการสินค้าชุมชน</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <?php if ($is_login): ?>
                    <span class="navbar-text me-3 text-white">
                        <?= h($_SESSION['name'] ?? $_SESSION['username']) ?>
                        <span class="badge bg-light text-success"><?= $is_admin ? 'ผู้ดูแลระบบ' : 'สมาชิก' ?></span>
                    </span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-light btn-sm me-2">เข้าสู่ระบบ</a>
                    <a href="register.php" class="btn btn-outline-light btn-sm">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>