<?php
require_once 'connect.php';
checkAdmin();
$nav_active = 'admin';

$msg = '';
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {                                   // เพิ่ม
        $name = trim($_POST['place_type_name'] ?? '');
        if ($name !== '') {
            $id = next_id($conn, 'place_type', 'place_type_id', 'PT', 2);
            $st = $conn->prepare("INSERT INTO place_type (place_type_id, place_type_name) VALUES (?, ?)");
            $st->bind_param("ss", $id, $name);
            $msg = $st->execute()
                ? '<div class="alert alert-success">เพิ่มประเภทสถานที่ ' . h($id) . ' เรียบร้อยแล้ว</div>'
                : '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเพิ่มข้อมูล</div>';
        }
    } elseif ($action === 'edit') {                            // แก้ไข
        $id   = trim($_POST['place_type_id'] ?? '');
        $name = trim($_POST['place_type_name'] ?? '');
        if ($id !== '' && $name !== '') {
            $st = $conn->prepare("UPDATE place_type SET place_type_name = ? WHERE place_type_id = ?");
            $st->bind_param("ss", $name, $id);
            $msg = $st->execute()
                ? '<div class="alert alert-success">แก้ไขข้อมูลประเภทสถานที่เรียบร้อยแล้ว</div>'
                : '<div class="alert alert-danger">เกิดข้อผิดพลาดในการแก้ไขข้อมูล</div>';
        }
    } elseif ($action === 'delete') {                          // ลบ
        $id = trim($_POST['place_type_id'] ?? '');
        $c = $conn->prepare("SELECT COUNT(*) c FROM place WHERE place_type_id = ?");
        $c->bind_param("s", $id);
        $c->execute();
        $used = (int)$c->get_result()->fetch_assoc()['c'];
        if ($used > 0) {
            $msg = '<div class="alert alert-warning">ลบไม่ได้ เพราะมีสถานที่ ' . $used . ' แห่งใช้ประเภทนี้อยู่ (ย้ายหรือลบสถานที่เหล่านั้นก่อน)</div>';
        } else {
            $st = $conn->prepare("DELETE FROM place_type WHERE place_type_id = ?");
            $st->bind_param("s", $id);
            $msg = $st->execute()
                ? '<div class="alert alert-success">ลบประเภทสถานที่เรียบร้อยแล้ว</div>'
                : '<div class="alert alert-danger">เกิดข้อผิดพลาดในการลบข้อมูล</div>';
        }
    }
}

// ค้นหา
$sql = "SELECT t.*, (SELECT COUNT(*) FROM place p WHERE p.place_type_id = t.place_type_id) AS n_places
        FROM place_type t WHERE 1=1";
$params = []; $bind = "";
if ($search !== '') {
    $sql .= " AND (t.place_type_id LIKE ? OR t.place_type_name LIKE ?)";
    $kw = "%$search%"; $params = [$kw, $kw]; $bind = "ss";
}
$sql .= " ORDER BY t.place_type_id";
$st = $conn->prepare($sql);
if ($params) $st->bind_param($bind, ...$params);
$st->execute();
$types = $st->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทสถานที่ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container pb-5">
    <h2 class="text-danger mb-3">จัดการประเภทสถานที่ (Admin)</h2>
    <?= $msg ?>

    <form method="GET" class="row g-2 mb-4 bg-white p-3 rounded shadow-sm">
        <div class="col-md-9">
            <input type="text" name="search" class="form-control" placeholder="ค้นหาตามรหัส หรือชื่อประเภทสถานที่..." value="<?= h($search) ?>">
        </div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">🔍 ค้นหา</button></div>
    </form>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">➕ เพิ่มประเภทสถานที่ใหม่ <small class="text-white-50">(รหัสสร้างอัตโนมัติ)</small></div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <input type="hidden" name="action" value="add">
                <div class="col-md-10">
                    <input type="text" name="place_type_name" class="form-control" placeholder="ชื่อประเภท (เช่น ที่พัก, สถานีตำรวจ)" required>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-success w-100">บันทึก</button></div>
            </form>
        </div>
    </div>

    <table class="table table-bordered bg-white shadow-sm align-middle">
        <thead class="table-dark">
            <tr><th width="15%">รหัส</th><th>ชื่อประเภทสถานที่</th><th width="15%">จำนวนสถานที่</th><th width="25%">จัดการ</th></tr>
        </thead>
        <tbody>
        <?php if ($types->num_rows > 0): while ($row = $types->fetch_assoc()): ?>
            <tr>
                <td><?= h($row['place_type_id']) ?></td>
                <td><?= h($row['place_type_name']) ?></td>
                <td><?= (int)$row['n_places'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm me-1 btn-edit"
                            data-id="<?= h($row['place_type_id']) ?>" data-name="<?= h($row['place_type_name']) ?>">✏️ แก้ไข</button>
                    <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบประเภทนี้?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="place_type_id" value="<?= h($row['place_type_id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">🗑️ ลบ</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="4" class="text-center text-muted">ไม่พบข้อมูล</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">✏️ แก้ไขประเภทสถานที่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <div class="mb-3">
                        <label class="form-label">รหัสประเภทสถานที่</label>
                        <input type="text" id="edit_id" name="place_type_id" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อประเภทสถานที่</label>
                        <input type="text" id="edit_name" name="place_type_name" class="form-control" required>
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
        document.getElementById('edit_id').value = b.dataset.id;
        document.getElementById('edit_name').value = b.dataset.name;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});
</script>
</body>
</html>
