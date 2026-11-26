<?php
require_once '../db.php';
require_login(['admin']);

// ดึงรายการแผนก/ระดับชั้นเก็บเป็น array (ใช้ทั้งสร้าง dropdown และตรวจค่าที่ส่งมา)
$departments = mysqli_fetch_all(mysqli_query($conn, 'SELECT id, name FROM departments ORDER BY name'), MYSQLI_ASSOC);
$levels      = mysqli_fetch_all(mysqli_query($conn, 'SELECT id, name FROM levels ORDER BY id'), MYSQLI_ASSOC);
$departmentIds = array_map('intval', array_column($departments, 'id'));
$levelIds      = array_map('intval', array_column($levels, 'id'));

// ค่าเริ่มต้นของฟอร์ม
$full_name = $email = $role = $position = '';
$department_id = $level_id = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';
    $position  = trim($_POST['position'] ?? '');

    // ค่าว่างจาก "-- ไม่ระบุ --" ต้องแปลงเป็น NULL ไม่งั้นจะกลายเป็น 0 แล้วติด FK (error 1452)
    $department_id = ($_POST['department_id'] ?? '') !== '' ? (int) $_POST['department_id'] : null;
    $level_id      = ($_POST['level_id'] ?? '') !== '' ? (int) $_POST['level_id'] : null;

    // ตรวจจากเร็วไปช้า: ค่าว่าง/รูปแบบก่อน แล้วค่อยถามฐานข้อมูล
    if ($full_name === '') {
        $error = 'กรุณากรอกชื่อ-นามสกุล';
    } elseif (mb_strlen($full_name) > 150) {
        $error = 'ชื่อ-นามสกุลยาวเกิน 150 ตัวอักษร';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif (strlen($password) < 8) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร';
    } elseif (!array_key_exists($role, $roles)) {
        $error = 'กรุณาเลือกบทบาท';
    } elseif ($department_id !== null && !in_array($department_id, $departmentIds, true)) {
        $error = 'ไม่พบแผนกที่เลือก';
    } elseif ($level_id !== null && !in_array($level_id, $levelIds, true)) {
        $error = 'ไม่พบระดับชั้นที่เลือก';
    } elseif (mb_strlen($position) > 100) {
        $error = 'ตำแหน่งยาวเกิน 100 ตัวอักษร';
    } else {
        // ตรวจอีเมลซ้ำในฐานข้อมูล (ไม่งั้นจะเจอ error 1062)
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $error = 'อีเมลนี้มีผู้ใช้แล้ว';
        }
    }

    if ($error === '') {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);   // เก็บเฉพาะ hash
        $positionValue = $position !== '' ? $position : null;

        // 7 ค่า → 7 เครื่องหมาย ? → ชนิดข้อมูล 7 ตัว 'ssssiis'
        $stmt = mysqli_prepare($conn,
            'INSERT INTO users (email, password_hash, full_name, role, department_id, level_id, position)
             VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssssiis',
            $email, $password_hash, $full_name, $role, $department_id, $level_id, $positionValue);

        try {
            mysqli_stmt_execute($stmt);
            flash('success', 'เพิ่มผู้ใช้ "' . $full_name . '" เรียบร้อยแล้ว');
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $error = $ex->getCode() === 1062 ? 'อีเมลนี้มีผู้ใช้แล้ว' : 'บันทึกไม่สำเร็จ กรุณาลองใหม่';
        }
    }
}

$isEdit = false;
$base = '../';
$pageTitle = 'เพิ่มผู้ใช้';
$activeMenu = 'users';
require '../includes/header.php';
?>
<h1 class="h4 fw-semibold mb-3">เพิ่มผู้ใช้</h1>
<?php require '_form.php'; ?>
<?php require '../includes/footer.php'; ?>
