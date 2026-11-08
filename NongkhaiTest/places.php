<?php
require_once 'connect.php';
checkMember();
$nav_active = 'places';

$search      = trim($_GET['search'] ?? '');
$type_filter = trim($_GET['type_id'] ?? '');
$dist_filter = trim($_GET['district_id'] ?? '');

$types     = $conn->query("SELECT * FROM place_type ORDER BY place_type_id")->fetch_all(MYSQLI_ASSOC);
$districts = $conn->query("SELECT * FROM district ORDER BY district_id")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, d.district_name, pt.place_type_name,
        (SELECT link_file FROM place_image pi WHERE pi.place_id = p.place_id ORDER BY pi.place_image_id LIMIT 1) AS image_file
        FROM place p
        LEFT JOIN district d ON p.district_id = d.district_id
        LEFT JOIN place_type pt ON p.place_type_id = pt.place_type_id
        WHERE 1=1";
$params = []; $bind = "";
if ($search !== '') {
    $sql .= " AND (p.place_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $bind .= "ss";
}
if ($type_filter !== '') { $sql .= " AND p.place_type_id = ?"; $params[] = $type_filter; $bind .= "s"; }
if ($dist_filter !== '') { $sql .= " AND p.district_id = ?";   $params[] = $dist_filter; $bind .= "s"; }
$sql .= " ORDER BY p.place_type_id, p.place_id";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($bind, ...$params);
$stmt->execute();
$places = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานที่ - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5">
    <h2 class="text-success mb-3">ค้นหาและดูข้อมูลสถานที่ในจังหวัดหนองคาย</h2>

    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสถานที่ หรือรายละเอียด..." value="<?= h($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="type_id" class="form-select">
                <option value="">-- ทุกประเภทสถานที่ --</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= h($t['place_type_id']) ?>" <?= $type_filter === $t['place_type_id'] ? 'selected' : '' ?>><?= h($t['place_type_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="district_id" class="form-select">
                <option value="">-- ทุกอำเภอ --</option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?= h($d['district_id']) ?>" <?= $dist_filter === $d['district_id'] ? 'selected' : '' ?>>อ.<?= h($d['district_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-success flex-fill">ค้นหา</button>
            <a href="places.php" class="btn btn-outline-secondary">ล้าง</a>
        </div>
    </form>

    <p class="text-muted">พบ <?= (int)$places->num_rows ?> รายการ</p>

    <div class="row">
        <?php if ($places->num_rows > 0): ?>
            <?php while ($row = $places->fetch_assoc()): $gl = gps_link($row['gps']); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <a href="place_view.php?id=<?= urlencode($row['place_id']) ?>">
                            <img src="<?= h(img_src($row['image_file'])) ?>" class="card-img-top" alt="<?= h($row['place_name']) ?>" style="height: 200px; object-fit: cover;">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-info text-dark mb-2 align-self-start"><?= h($row['place_type_name'] ?? 'ไม่ระบุประเภท') ?></span>
                            <h5 class="card-title"><?= h($row['place_name']) ?></h5>
                            <?php if (!empty($row['district_name'])): ?>
                                <p class="card-text text-muted small mb-1">อ.<?= h($row['district_name']) ?></p>
                            <?php endif; ?>
                            <p class="card-text flex-grow-1 small">
                                <?= h(mb_strimwidth(str_replace("\n", ' ', $row['description'] ?? ''), 0, 160, '...', 'UTF-8')) ?>
                            </p>
                            <div class="d-flex gap-2 mt-2">
                                <a href="place_view.php?id=<?= urlencode($row['place_id']) ?>" class="btn btn-outline-success btn-sm">รายละเอียด</a>
                                <?php if ($gl): ?>
                                    <a href="<?= h($gl) ?>" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm">📍 Google Maps</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning">ไม่พบข้อมูลสถานที่ตรงกับเงื่อนไขการค้นหา</div></div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
