<?php
require_once 'connect.php';
checkMember();
$nav_active = 'places';

$id = trim($_GET['id'] ?? '');
$s = $conn->prepare("SELECT p.*, t.place_type_name, d.district_name
                     FROM place p
                     LEFT JOIN place_type t ON p.place_type_id = t.place_type_id
                     LEFT JOIN district d ON p.district_id = d.district_id
                     WHERE p.place_id = ?");
$s->bind_param("s", $id);
$s->execute();
$p = $s->get_result()->fetch_assoc();

$imgs = [];
$prods = [];
if ($p) {
    $si = $conn->prepare("SELECT link_file FROM place_image WHERE place_id = ? ORDER BY place_image_id");
    $si->bind_param("s", $id);
    $si->execute();
    $ri = $si->get_result();
    while ($im = $ri->fetch_assoc()) $imgs[] = $im['link_file'];

    $sp = $conn->prepare("SELECT product_id, product_name FROM community_product WHERE place_id = ?");
    $sp->bind_param("s", $id);
    $sp->execute();
    $prods = $sp->get_result()->fetch_all(MYSQLI_ASSOC);
}
$gl = $p ? gps_link($p['gps']) : null;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดสถานที่ - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5" style="max-width: 900px;">
    <a href="places.php" class="btn btn-outline-secondary btn-sm mb-3">&laquo; กลับหน้าค้นหาสถานที่</a>
<?php if (!$p): ?>
    <div class="alert alert-warning">ไม่พบข้อมูลสถานที่</div>
<?php else: ?>
    <div class="card shadow-sm">
        <?php if ($imgs): ?>
            <div class="row g-0">
                <?php foreach ($imgs as $f): ?>
                    <div class="<?= count($imgs) > 1 ? 'col-md-6' : 'col-12' ?>">
                        <img src="<?= h($f) ?>" class="w-100" style="max-height: 380px; object-fit: cover;" alt="<?= h($p['place_name']) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="card-body p-4">
            <span class="badge bg-info text-dark mb-2"><?= h($p['place_type_name'] ?? 'ไม่ระบุประเภท') ?></span>
            <h3 class="text-success"><?= h($p['place_name']) ?></h3>
            <?php if (!empty($p['district_name'])): ?>
                <p class="text-muted">อำเภอ<?= h($p['district_name']) ?> จังหวัดหนองคาย</p>
            <?php endif; ?>
            <p><?= !empty($p['description']) ? nl2br(h($p['description'])) : '-' ?></p>

            <?php if ($prods): ?>
                <h6 class="mt-4">สินค้าชุมชนที่จำหน่ายที่นี่</h6>
                <ul><?php foreach ($prods as $pr): ?><li><?= h($pr['product_name']) ?></li><?php endforeach; ?></ul>
            <?php endif; ?>

            <hr>
            <?php if ($gl): ?>
                <a href="<?= h($gl) ?>" target="_blank" rel="noopener" class="btn btn-danger">📍 เปิดตำแหน่งใน Google Maps</a>
            <?php else: ?>
                <span class="text-muted">ยังไม่มีข้อมูลตำแหน่งบน Google Maps</span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
