<?php
require_once 'connect.php';
checkMember();
$nav_active = 'products';

$search      = trim($_GET['search'] ?? '');
$type_filter = trim($_GET['type_id'] ?? '');
$ptypes = $conn->query("SELECT * FROM product_type ORDER BY product_type_id")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT p.*, pt.product_type_name, pl.place_name,
        (SELECT link_file FROM product_image pi WHERE pi.product_id = p.product_id ORDER BY pi.product_image_id LIMIT 1) AS image_file
        FROM community_product p
        LEFT JOIN product_type pt ON p.product_type_id = pt.product_type_id
        LEFT JOIN place pl ON p.place_id = pl.place_id
        WHERE 1=1";
$params = []; $bind = "";
if ($search !== '') {
    $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $bind .= "ss";
}
if ($type_filter !== '') { $sql .= " AND p.product_type_id = ?"; $params[] = $type_filter; $bind .= "s"; }
$sql .= " ORDER BY p.product_id";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($bind, ...$params);
$stmt->execute();
$products = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้าชุมชน - หนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5">
    <h2 class="text-success mb-3">สินค้าชุมชน จังหวัดหนองคาย</h2>

    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสินค้า หรือรายละเอียด..." value="<?= h($search) ?>">
        </div>
        <div class="col-md-4">
            <select name="type_id" class="form-select">
                <option value="">-- ทุกประเภทสินค้า --</option>
                <?php foreach ($ptypes as $t): ?>
                    <option value="<?= h($t['product_type_id']) ?>" <?= $type_filter === $t['product_type_id'] ? 'selected' : '' ?>><?= h($t['product_type_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-success flex-fill">ค้นหา</button>
            <a href="products.php" class="btn btn-outline-secondary">ล้าง</a>
        </div>
    </form>

    <div class="row">
        <?php if ($products->num_rows > 0): ?>
            <?php while ($row = $products->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= h(img_src($row['image_file'])) ?>" class="card-img-top" alt="<?= h($row['product_name']) ?>" style="height: 220px; object-fit: cover;">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?= h($row['product_type_name'] ?? 'ทั่วไป') ?></span>
                            <h5 class="card-title"><?= h($row['product_name']) ?></h5>
                            <?php if (!empty($row['place_name'])): ?>
                                <p class="card-text small text-muted">จำหน่าย ณ: <a href="place_view.php?id=<?= urlencode($row['place_id']) ?>"><?= h($row['place_name']) ?></a></p>
                            <?php endif; ?>
                            <p class="card-text small"><?= linkify($row['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning">ไม่พบสินค้าชุมชน</div></div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
