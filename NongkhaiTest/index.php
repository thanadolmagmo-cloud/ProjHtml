<?php
require_once 'connect.php';
$nav_active = 'home';
$is_login = isset($_SESSION['username']);

// สถิติจากฐานข้อมูล
$n_place   = $conn->query("SELECT COUNT(*) c FROM place")->fetch_assoc()['c'];
$n_product = $conn->query("SELECT COUNT(*) c FROM community_product")->fetch_assoc()['c'];
$districts = $conn->query("SELECT district_name FROM district ORDER BY district_id")->fetch_all(MYSQLI_ASSOC);
$by_type   = $conn->query("SELECT t.place_type_name, COUNT(p.place_id) c
                           FROM place_type t LEFT JOIN place p ON p.place_type_id = t.place_type_id
                           GROUP BY t.place_type_id, t.place_type_name ORDER BY t.place_type_id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสารสนเทศท่องเที่ยวจังหวัดหนองคาย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container pb-5">
    <div class="p-4 p-md-5 mb-4 bg-white rounded-3 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 fw-bold text-success">ยินดีต้อนรับสู่จังหวัดหนองคาย</h1>
                <p class="fs-5">ประตูสู่เวียงจันทน์ เมืองชายแดนริมฝั่งแม่น้ำโขง เต็มไปด้วยวัฒนธรรม ธรรมชาติ และสินค้าชุมชนอันทรงคุณค่า</p>
                <p class="text-muted mb-0">คำขวัญ: <em>วีรกรรมปราบฮ่อ หลวงพ่อพระใส สะพานไทย-ลาว</em></p>
            </div>
            <div class="col-md-4 text-center mt-3 mt-md-0">
                <img src="nongkhai.png" class="img-fluid" style="max-height: 200px;" alt="ตราประจำจังหวัดหนองคาย">
            </div>
        </div>
    </div>

    <?php if (!$is_login): ?>
        <div class="alert alert-info">
            <strong>คำแนะนำ:</strong> <a href="login.php" class="alert-link">เข้าสู่ระบบ</a> หรือ
            <a href="register.php" class="alert-link">สมัครสมาชิก</a>
            เพื่อค้นหาสถานที่ ดูตำแหน่งบน Google Maps และค้นหาสินค้าชุมชน
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <a href="places.php" class="btn btn-success w-100 py-3">📍 ค้นหาสถานที่ (<?= (int)$n_place ?> แห่ง)</a>
            </div>
            <div class="col-md-6">
                <a href="products.php" class="btn btn-outline-success w-100 py-3">🧺 สินค้าชุมชน (<?= (int)$n_product ?> รายการ)</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="text-success">ข้อมูลทั่วไปของจังหวัดหนองคาย</h4>
                    <p>จังหวัดหนองคายตั้งอยู่ในภาคตะวันออกเฉียงเหนือของประเทศไทย ทางทิศเหนือติดกับ สปป.ลาว โดยมีแม่น้ำโขงเป็นเส้นแบ่งพรมแดน
                       ทางทิศใต้ติดกับจังหวัดอุดรธานี ทางทิศตะวันออกติดกับจังหวัดบึงกาฬ และทางทิศตะวันตกเฉียงใต้ติดกับจังหวัดหนองบัวลำภู</p>
                    <h6 class="mt-3">สิ่งสำคัญตามคำขวัญ</h6>
                    <ul>
                        <li><strong>วีรกรรมปราบฮ่อ</strong> – เหตุการณ์ทางประวัติศาสตร์ที่เกี่ยวข้องกับการปราบกลุ่มฮ่อในอดีต</li>
                        <li><strong>หลวงพ่อพระใส</strong> – พระพุทธรูปคู่บ้านคู่เมือง ประดิษฐานที่วัดโพธิ์ชัย อำเภอเมืองหนองคาย</li>
                        <li><strong>สะพานไทย-ลาว</strong> – สะพานมิตรภาพไทย-ลาว ข้ามแม่น้ำโขงเชื่อมหนองคายกับนครหลวงเวียงจันทน์</li>
                    </ul>
                    <h6>อำเภอในจังหวัด (<?= count($districts) ?> อำเภอ)</h6>
                    <p>
                        <?php foreach ($districts as $d): ?>
                            <span class="badge bg-success-subtle text-success-emphasis border me-1 mb-1">อ.<?= h($d['district_name']) ?></span>
                        <?php endforeach; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="text-success">แผนที่จังหวัด</h5>
                    <img src="first.jpg" class="img-fluid rounded" alt="แผนที่จังหวัดหนองคาย">
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="text-success">จำนวนสถานที่</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($by_type as $t): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <?= h($t['place_type_name']) ?>
                                <span class="badge bg-success rounded-pill"><?= (int)$t['c'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
