<?php
require_once 'connect.php';
checkAdmin();
$nav_active = 'admin';

$msg = '';
$search = trim($_GET['search'] ?? '');
$dir = 'uploads/product';

function nul($v) { $v = trim($v ?? ''); return $v === '' ? null : $v; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name  = trim($_POST['product_name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $place = nul($_POST['place_id'] ?? '');
        $ptype = nul($_POST['product_type_id'] ?? '');

        if ($name === '') {
            $msg = '<div class="alert alert-danger">กรุณากรอกชื่อสินค้า</div>';
        } elseif ($action === 'add') {                                   // เพิ่ม
            $id = next_id($conn, 'community_product', 'product_id', 'C', 3);
            $st = $conn->prepare("INSERT INTO community_product (product_id, product_name, description, place_id, product_type_id) VALUES (?,?,?,?,?)");
            $st->bind_param("sssss", $id, $name, $desc, $place, $ptype);
            if ($st->execute()) {
                [$n, $errs] = save_images($conn, 'images', 'product_image', 'product_image_id', 'CI', 'product_id', $id, $dir);
                $msg = '<div class="alert alert-success">เพิ่มสินค้า ' . h($id) . ' เรียบร้อยแล้ว' . ($n ? " (อัปโหลดรูป $n รูป)" : '') . '</div>';
                if ($errs) $msg .= '<div class="alert alert-warning">' . h(implode(' / ', $errs)) . '</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเพิ่มข้อมูล</div>';
            }
        } else {                                                         // แก้ไข
            $id = trim($_POST['product_id'] ?? '');
            $st = $conn->prepare("UPDATE community_product SET product_name=?, description=?, place_id=?, product_type_id=? WHERE product_id=?");
            $st->bind_param("sssss", $name, $desc, $place, $ptype, $id);
            if ($st->execute()) {
                foreach ((array)($_POST['del_img'] ?? []) as $img_id) {
                    $chk = $conn->prepare("SELECT 1 FROM product_image WHERE product_image_id=? AND product_id=?");
                    $chk->bind_param("ss", $img_id, $id);
                    $chk->execute();
                    if ($chk->get_result()->num_rows) delete_image($conn, 'product_image', 'product_image_id', $img_id);
                }
                [$n, $errs] = save_images($conn, 'images', 'product_image', 'product_image_id', 'CI', 'product_id', $id, $dir);
                $msg = '<div class="alert alert-success">แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว</div>';
                if ($errs) $msg .= '<div class="alert alert-warning">' . h(implode(' / ', $errs)) . '</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการแก้ไขข้อมูล</div>';
            }
        }
    } elseif ($action === 'delete') {                                    // ลบ
        $id = trim($_POST['product_id'] ?? '');
        delete_images_of($conn, 'product_image', 'product_image_id', 'product_id', $id);
        $st = $conn->prepare("DELETE FROM community_product WHERE product_id = ?");
        $st->bind_param("s", $id);
        $msg = $st->execute()
            ? '<div class="alert alert-success">ลบสินค้าเรียบร้อยแล้ว</div>'
            : '<div class="alert alert-danger">เกิดข้อผิดพลาดในการลบข้อมูล</div>';
    }
}

// ค้นหา
$sql = "SELECT cp.*, pt.product_type_name, pl.place_name,
        (SELECT link_file FROM product_image pi WHERE pi.product_id = cp.product_id ORDER BY pi.product_image_id LIMIT 1) AS image_file
        FROM community_product cp
        LEFT JOIN product_type pt ON cp.product_type_id = pt.product_type_id
        LEFT JOIN place pl ON cp.place_id = pl.place_id
        WHERE 1=1";
$params = []; $bind = "";
if ($search !== '') {
    $sql .= " AND (cp.product_id LIKE ? OR cp.product_name LIKE ? OR cp.description LIKE ? OR pt.product_type_name LIKE ?)";
    $kw = "%$search%"; $params = [$kw, $kw, $kw, $kw]; $bind = "ssss";
}
$sql .= " ORDER BY cp.product_id DESC";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($bind, ...$params);
$st->execute();
$products = $st->get_result();

$places = $conn->query("SELECT place_id, place_name FROM place ORDER BY place_name")->fetch_all(MYSQLI_ASSOC);
$ptypes = $conn->query("SELECT * FROM product_type ORDER BY product_type_id")->fetch_all(MYSQLI_ASSOC);
$imgmap = images_map($conn, 'product_image', 'product_image_id', 'product_id');

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
    <title>จัดการสินค้าชุมชน - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5">
    <h2 class="text-danger mb-3">จัดการสินค้าชุมชน (Admin)</h2>
    <?= $msg ?>

    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-9">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาตามรหัส ชื่อสินค้า ประเภท หรือรายละเอียด..." value="<?= h($search) ?>">
        </div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">🔍 ค้นหาสินค้า</button></div>
    </form>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">➕ เพิ่มสินค้าชุมชนใหม่ <small class="text-white-50">(รหัสสร้างอัตโนมัติ)</small></div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="action" value="add">
                <div class="col-md-6">
                    <label class="form-label">ชื่อสินค้า</label>
                    <input type="text" name="product_name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">ประเภทสินค้า</label>
                    <select name="product_type_id" class="form-select"><?= options($ptypes, 'product_type_id', 'product_type_name', true) ?></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">จำหน่าย ณ สถานที่</label>
                    <select name="place_id" class="form-select"><?= options($places, 'place_id', 'place_name', true) ?></select>
                </div>
                <div class="col-12">
                    <label class="form-label">รูปภาพ (เลือกได้หลายรูป JPG/PNG/WEBP ไม่เกิน 2MB)</label>
                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                </div>
                <div class="col-12">
                    <label class="form-label">รายละเอียด <small class="text-muted">(ใส่ลิงก์ Google Maps ของผู้ผลิตได้ เช่น “พิกัดผู้ผลิต: https://maps.app.goo.gl/...”)</small></label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12"><button type="submit" class="btn btn-success">บันทึกข้อมูลสินค้า</button></div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered bg-white shadow-sm align-middle">
        <thead class="table-dark">
            <tr><th>รูปภาพ</th><th>รหัส</th><th>ชื่อสินค้า</th><th>ประเภท</th><th>จำหน่าย ณ</th><th width="170">จัดการ</th></tr>
        </thead>
        <tbody>
        <?php if ($products->num_rows > 0): while ($row = $products->fetch_assoc()):
            $payload = [
                'product_id' => $row['product_id'], 'product_name' => $row['product_name'],
                'description' => $row['description'], 'place_id' => $row['place_id'],
                'product_type_id' => $row['product_type_id'],
                'images' => $imgmap[$row['product_id']] ?? [],
            ];
        ?>
            <tr>
                <td width="100"><img src="<?= h(img_src($row['image_file'])) ?>" class="img-thumbnail" style="height: 50px; width: 80px; object-fit: cover;" alt=""></td>
                <td><?= h($row['product_id']) ?></td>
                <td><?= h($row['product_name']) ?></td>
                <td><?= h($row['product_type_name'] ?? '-') ?></td>
                <td><?= h($row['place_name'] ?? '-') ?></td>
                <td>
                    <button class="btn btn-warning btn-sm me-1 btn-edit" data-product="<?= h(json_encode($payload, JSON_UNESCAPED_UNICODE)) ?>">✏️ แก้ไข</button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบสินค้านี้?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="product_id" value="<?= h($row['product_id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูลสินค้า</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">✏️ แก้ไขข้อมูลสินค้า</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="action" value="edit">
                    <div class="col-md-3">
                        <label class="form-label">รหัสสินค้า</label>
                        <input type="text" id="edit_product_id" name="product_id" class="form-control" readonly>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" id="edit_product_name" name="product_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ประเภทสินค้า</label>
                        <select id="edit_product_type_id" name="product_type_id" class="form-select"><?= options($ptypes, 'product_type_id', 'product_type_name', true) ?></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">จำหน่าย ณ สถานที่</label>
                        <select id="edit_place_id" name="place_id" class="form-select"><?= options($places, 'place_id', 'place_name', true) ?></select>
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
        var p = JSON.parse(b.dataset.product);
        document.getElementById('edit_product_id').value = p.product_id;
        document.getElementById('edit_product_name').value = p.product_name || '';
        document.getElementById('edit_product_type_id').value = p.product_type_id || '';
        document.getElementById('edit_place_id').value = p.place_id || '';
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
        new bootstrap.Modal(document.getElementById('editProductModal')).show();
    });
});
</script>
</body>
</html>
