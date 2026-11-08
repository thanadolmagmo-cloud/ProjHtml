<?php
// ไฟล์กลาง: เชื่อมต่อฐานข้อมูล + session + ฟังก์ชันช่วยที่ใช้ร่วมกันทุกหน้า
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "nongkhai";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ---------- สิทธิ์การเข้าใช้งาน ----------
function checkAdmin() {
    if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
}
function checkMember() { // สมาชิก และ admin
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
}

// ---------- ฟังก์ชันช่วยแสดงผล ----------
function h($v) {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

// ช่อง gps เก็บ "ลิงก์ Google Maps" (เช่น https://maps.app.goo.gl/xxxx) คืนค่า null ถ้าว่าง/ไม่ใช่ลิงก์
function gps_link($gps) {
    $gps = trim($gps ?? '');
    if ($gps !== '' && preg_match('#^https?://#i', $gps)) {
        return $gps;
    }
    return null;
}

// แปลงข้อความที่มี URL ให้เป็นลิงก์กดได้ (ใช้กับรายละเอียดสินค้า "พิกัดผู้ผลิต: https://...")
function linkify($text) {
    $safe = h($text);
    $safe = preg_replace(
        '#(https?://[^\s<]+)#i',
        '<a href="$1" target="_blank" rel="noopener">เปิดใน Google Maps 📍</a>',
        $safe
    );
    return nl2br($safe);
}

// path รูปภาพ (ในฐานข้อมูลเก็บเป็น uploads/place/PI0001.jpg) ถ้าไม่มีรูปให้ใช้รูปแทน
function img_src($path) {
    if (!empty($path)) return $path;
    return 'data:image/svg+xml;utf8,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="250"><rect width="100%" height="100%" fill="#e9ecef"/>'
        . '<text x="50%" y="50%" fill="#6c757d" font-size="20" text-anchor="middle" font-family="sans-serif">No Image</text></svg>'
    );
}

// ---------- ฟังก์ชันจัดการรหัสและรูปภาพ ----------
// สร้างรหัสถัดไป เช่น P022 (ชื่อตาราง/คอลัมน์เป็นค่าคงที่ในโค้ด ไม่ได้มาจากผู้ใช้)
function next_id($conn, $table, $col, $prefix, $pad) {
    $start = strlen($prefix) + 1;
    $r = $conn->query("SELECT MAX(CAST(SUBSTRING($col, $start) AS UNSIGNED)) AS m FROM $table");
    $row = $r->fetch_assoc();
    return $prefix . str_pad(((int)($row['m'] ?? 0)) + 1, $pad, "0", STR_PAD_LEFT);
}

// อัปโหลดรูปหลายไฟล์ (input name="images[]") แล้วบันทึกลงตารางรูปภาพ
// คืนค่า [จำนวนที่บันทึกได้, [ข้อความผิดพลาด]]
function save_images($conn, $field, $table, $idcol, $prefix, $fkcol, $fkval, $dir) {
    $errs = []; $count = 0;
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]['name'])) return [$count, $errs];
    if (!is_dir($dir)) { mkdir($dir, 0777, true); }
    $allowed = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];

    foreach ($_FILES[$field]['name'] as $i => $orig) {
        $err = $_FILES[$field]['error'][$i];
        if ($err == UPLOAD_ERR_NO_FILE) continue;
        if ($err != UPLOAD_ERR_OK) { $errs[] = "$orig (อัปโหลดไม่สำเร็จ)"; continue; }
        if ($_FILES[$field]['size'][$i] > 2 * 1024 * 1024) { $errs[] = "$orig (ใหญ่เกิน 2 MB)"; continue; }
        $tmp  = $_FILES[$field]['tmp_name'][$i];
        $info = @getimagesize($tmp);
        $mime = $info['mime'] ?? '';
        if (!isset($allowed[$mime])) { $errs[] = "$orig (ต้องเป็น JPG, PNG หรือ WEBP)"; continue; }

        $id   = next_id($conn, $table, $idcol, $prefix, 4);
        $path = $dir . "/" . $id . "." . $allowed[$mime];
        if (!move_uploaded_file($tmp, $path)) { $errs[] = "$orig (บันทึกไฟล์ไม่สำเร็จ)"; continue; }

        $s = $conn->prepare("INSERT INTO $table ($idcol, link_file, $fkcol) VALUES (?,?,?)");
        $s->bind_param("sss", $id, $path, $fkval);
        $s->execute();
        $count++;
    }
    return [$count, $errs];
}

// ลบรูป 1 รูป (ทั้งแถวในฐานข้อมูลและไฟล์จริง)
function delete_image($conn, $table, $idcol, $id) {
    $s = $conn->prepare("SELECT link_file FROM $table WHERE $idcol=?");
    $s->bind_param("s", $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) return;
    $f = $row['link_file'];
    if (strpos($f, "uploads/") === 0 && strpos($f, "..") === false && is_file($f)) { @unlink($f); }
    $d = $conn->prepare("DELETE FROM $table WHERE $idcol=?");
    $d->bind_param("s", $id);
    $d->execute();
}

// ลบรูปทั้งหมดของสถานที่/สินค้า 1 รายการ
function delete_images_of($conn, $table, $idcol, $fkcol, $fkval) {
    $s = $conn->prepare("SELECT $idcol FROM $table WHERE $fkcol=?");
    $s->bind_param("s", $fkval);
    $s->execute();
    $res = $s->get_result();
    $ids = [];
    while ($r = $res->fetch_assoc()) { $ids[] = $r[$idcol]; }
    foreach ($ids as $id) { delete_image($conn, $table, $idcol, $id); }
}

// ดึงรูปทั้งหมด จัดกลุ่มตาม id เจ้าของ  => [ 'P001' => [['id'=>..,'file'=>..], ...] ]
function images_map($conn, $table, $idcol, $fkcol) {
    $map = [];
    $r = $conn->query("SELECT $idcol AS id, link_file, $fkcol AS fk FROM $table ORDER BY $idcol");
    while ($x = $r->fetch_assoc()) { $map[$x['fk']][] = ['id' => $x['id'], 'file' => $x['link_file']]; }
    return $map;
}
?>
