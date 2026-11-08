<?php
require_once 'connect.php';
checkAdmin();
$nav_active = 'admin';

$msg = '';
$search = trim($_GET['search'] ?? '');
$dir = 'uploads/place';

function nul($v) { $v = trim($v ?? ''); return $v === '' ? null : $v; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['place_name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $gps  = nul($_POST['gps'] ?? '');
        $dist = nul($_POST['district_id'] ?? '');
        $type = nul($_POST['place_type_id'] ?? '');

        if ($name === '') {
            $msg = '<div class="alert alert-danger">กรุณากรอกชื่อสถานที่</div>';
        } elseif ($gps !== null && !preg_match('#^https?://#i', $gps)) {
            $msg = '<div class="alert alert-danger">ช่องพิกัดต้องเป็นลิงก์ Google Maps (ขึ้นต้นด้วย https://)</div>';
        } elseif ($action === 'add') {                                   // เพิ่ม
            $id = next_id($conn, 'place', 'place_id', 'P', 3);
            $st = $conn->prepare("INSERT INTO place (place_id, place_name, description, gps, district_id, place_type_id) VALUES (?,?,?,?,?,?)");
            $st->bind_param("ssssss", $id, $name, $desc, $gps, $dist, $type);
            if ($st->execute()) {
                [$n, $errs] = save_images($conn, 'images', 'place_image', 'place_image_id', 'PI', 'place_id', $id, $dir);
                $msg = '<div class="alert alert-success">เพิ่มสถานที่ ' . h($id) . ' เรียบร้อยแล้ว' . ($n ? " (อัปโหลดรูป $n รูป)" : '') . '</div>';
                if ($errs) $msg .= '<div class="alert alert-warning">' . h(implode(' / ', $errs)) . '</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเพิ่มข้อมูล</div>';
            }
        } else {                                                         // แก้ไข
            $id = trim($_POST['place_id'] ?? '');
            $st = $conn->prepare("UPDATE place SET place_name=?, description=?, gps=?, district_id=?, place_type_id=? WHERE place_id=?");
            $st->bind_param("ssssss", $name, $desc, $gps, $dist, $type, $id);
            if ($st->execute()) {
                foreach ((array)($_POST['del_img'] ?? []) as $img_id) {   // ลบรูปที่ติ๊กไว้
                    $chk = $conn->prepare("SELECT 1 FROM place_image WHERE place_image_id=? AND place_id=?");
                    $chk->bind_param("ss", $img_id, $id);
                    $chk->execute();
                    if ($chk->get_result()->num_rows) delete_image($conn, 'place_image', 'place_image_id', $img_id);
                }
                [$n, $errs] = save_images($conn, 'images', 'place_image', 'place_image_id', 'PI', 'place_id', $id, $dir);
                $msg = '<div class="alert alert-success">แก้ไขข้อมูลสถานที่เรียบร้อยแล้ว</div>';
                if ($errs) $msg .= '<div class="alert alert-warning">' . h(implode(' / ', $errs)) . '</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการแก้ไขข้อมูล</div>';
            }
        }
    } elseif ($action === 'delete') {                                    // ลบ
        $id = trim($_POST['place_id'] ?? '');
        delete_images_of($conn, 'place_image', 'place_image_id', 'place_id', $id);
        $u = $conn->prepare("UPDATE community_product SET place_id = NULL WHERE place_id = ?");
        $u->bind_param("s", $id);
        $u->execute();
        $st = $conn->prepare("DELETE FROM place WHERE place_id = ?");
        $st->bind_param("s", $id);
        $msg = $st->execute()
            ? '<div class="alert alert-success">ลบสถานที่เรียบร้อยแล้ว</div>'
            : '<div class="alert alert-danger">เกิดข้อผิดพลาดในการลบข้อมูล</div>';
    }
}

// ค้นหา
$sql = "SELECT p.*, d.district_name, pt.place_type_name,
        (SELECT link_file FROM place_image pi WHERE pi.place_id = p.place_id ORDER BY pi.place_image_id LIMIT 1) AS image_file
        FROM place p
        LEFT JOIN district d ON p.district_id = d.district_id
        LEFT JOIN place_type pt ON p.place_type_id = pt.place_type_id
        WHERE 1=1";
$params = []; $bind = "";
if ($search !== '') {
    $sql .= " AND (p.place_id LIKE ? OR p.place_name LIKE ? OR p.description LIKE ? OR d.district_name LIKE ? OR pt.place_type_name LIKE ?)";
    $kw = "%$search%"; $params = [$kw, $kw, $kw, $kw, $kw]; $bind = "sssss";
}
$sql .= " ORDER BY p.place_id DESC";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($bind, ...$params);
$st->execute();
$places = $st->get_result();

$districts = $conn->query("SELECT * FROM district ORDER BY district_id")->fetch_all(MYSQLI_ASSOC);
$ptypes    = $conn->query("SELECT * FROM place_type ORDER BY place_type_id")->fetch_all(MYSQLI_ASSOC);
$imgmap    = images_map($conn, 'place_image', 'place_image_id', 'place_id');

function options($rows, $idk, $namek, $blank) {
    $o = $blank ? '<option value="">-- ไม่ระบุ --</option>' : '';
    foreach ($rows as $r) $o .= '<option value="' . h($r[$idk]) . '">' . h($r[$namek]) . '</option>';
    return $o;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสถานที่ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5">
    <h2 class="text-danger mb-3">จัดการข้อมูลสถานที่ (Admin)</h2>
    <?= $msg ?>

    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-9">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาตามรหัส ชื่อสถานที่ อำเภอ ประเภท หรือรายละเอียด..." value="<?= h($search) ?>">
        </div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">🔍 ค้นหาสถานที่</button></div>
    </form>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">➕ เพิ่มสถานที่ใหม่ <small class="text-white-50">(รหัสสร้างอัตโนมัติ)</small></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-6">
                    <label class="form-label">ชื่อสถานที่</label>
                    <input type="text" name="place_name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">อำเภอ</label>
                    <select name="district_id" class="form-select"><?= options($districts, 'district_id', 'district_name', true) ?></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ประเภทสถานที่</label>
                    <select name="place_type_id" class="form-select" required><?= options($ptypes, 'place_type_id', 'place_type_name', false) ?></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">พิกัด Google Maps (วางลิงก์)</label>
                    <input type="url" name="gps" class="form-control" placeholder="https://maps.app.goo.gl/xxxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">รูปภาพ (เลือกได้หลายรูป JPG/PNG/WEBP ไม่เกิน 2MB)</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-success">บันทึกข้อมูลสถานที่</button></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered bg-white shadow-sm align-middle">
        <thead class="table-dark">
            <tr><th>รูปภาพ</th><th>รหัส</th><th>ชื่อสถานที่</th><th>อำเภอ</th><th>ประเภท</th><th>แผนที่</th><th width="170">จัดการ</th></tr>
        </thead>
        <tbody>
        <?php if ($places->num_rows > 0): while ($row = $places->fetch_assoc()):
            $gl = gps_link($row['gps']);
            $payload = [
                'place_id' => $row['place_id'], 'place_name' => $row['place_name'],
                'description' => $row['description'], 'gps' => $row['gps'],
                'district_id' => $row['district_id'], 'place_type_id' => $row['place_type_id'],
                'images' => $imgmap[$row['place_id']] ?? [],
            ];
        ?>
            <tr>
                <td width="100"><img src="<?= h(img_src($row['image_file'])) ?>" class="img-thumbnail" style="height: 50px; width: 80px; object-fit: cover;" alt=""></td>
                <td><?= h($row['place_id']) ?></td>
                <td><?= h($row['place_name']) ?></td>
                <td><?= h($row['district_name'] ?? '-') ?></td>
                <td><?= h($row['place_type_name'] ?? '-') ?></td>
                <td><?= $gl ? '<a href="' . h($gl) . '" target="_blank" rel="noopener">📍 เปิด</a>' : '-' ?></td>
                <td>
                    <button class="btn btn-warning btn-sm me-1 btn-edit" data-place="<?= h(json_encode($payload, JSON_UNESCAPED_UNICODE)) ?>">✏️ แก้ไข</button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบสถานที่นี้?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="place_id" value="<?= h($row['place_id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="7" class="text-center text-muted">ไม่พบข้อมูลสถานที่</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="modal fade" id="editPlaceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">✏️ แก้ไขข้อมูลสถานที่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="action" value="edit">
                    <div class="col-md-3">
                        <label class="form-label">รหัสสถานที่</label>
                        <input type="text" id="edit_place_id" name="place_id" class="form-control" readonly>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">ชื่อสถานที่</label>
                        <input type="text" id="edit_place_name" name="place_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">อำเภอ</label>
                        <select id="edit_district_id" name="district_id" class="form-select"><?= options($districts, 'district_id', 'district_name', true) ?></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ประเภทสถานที่</label>
                        <select id="edit_place_type_id" name="place_type_id" class="form-select" required><?= options($ptypes, 'place_type_id', 'place_type_name', false) ?></select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">พิกัด Google Maps (ลิงก์)</label>
                        <input type="url" id="edit_gps" name="gps" class="form-control" placeholder="https://maps.app.goo.gl/xxxxxxxx">
                    </div>
                    <div class="col-12">
                        <label class="form-label">รูปภาพปัจจุบัน (ติ๊กเพื่อลบ)</label>
                        <div id="edit_images" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">เพิ่มรูปใหม่</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="col-12">
                        <label class="form-label">รายละเอียด</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.btn-edit').forEach(function (b) {
    b.addEventListener('click', function () {
        var p = JSON.parse(b.dataset.place);
        document.getElementById('edit_place_id').value = p.place_id;
        document.getElementById('edit_place_name').value = p.place_name || '';
        document.getElementById('edit_district_id').value = p.district_id || '';
        document.getElementById('edit_place_type_id').value = p.place_type_id || '';
        document.getElementById('edit_gps').value = p.gps || '';
        document.getElementById('edit_description').value = p.description || '';
        var box = document.getElementById('edit_images');
        box.innerHTML = '';
        (p.images || []).forEach(function (im) {
            var d = document.createElement('label');
            d.className = 'border rounded p-1 text-center small';
            var img = document.createElement('img');
            img.src = im.file; img.style.cssText = 'height:70px;width:100px;object-fit:cover;display:block';
            var cb = document.createElement('input');
            cb.type = 'checkbox'; cb.name = 'del_img[]'; cb.value = im.id; cb.className = 'form-check-input me-1';
            d.appendChild(img); d.appendChild(cb); d.appendChild(document.createTextNode('ลบ'));
            box.appendChild(d);
        });
        if (!(p.images || []).length) box.innerHTML = '<span class="text-muted">ยังไม่มีรูปภาพ</span>';
        new bootstrap.Modal(document.getElementById('editPlaceModal')).show();
    });
});
</script>
</body>
</html>
