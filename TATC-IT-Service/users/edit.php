<?php
require_once '../db.php';
$me = require_login(['admin']);

$id = (int) ($_GET['id'] ?? 0);

// โหลดข้อมูลเดิม ถ้าไม่พบ (เช่น id=999) ให้กลับไปหน้ารายการ
$stmt = mysqli_prepare($conn, 'SELECT * FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    flash('warning', 'ไม่พบผู้ใช้ที่ต้องการแก้ไข');
    header('Location: index.php');
    exit;
}

$departments = mysqli_fetch_all(mysqli_query($conn, 'SELECT id, name FROM departments ORDER BY name'), MYSQLI_ASSOC);
$levels      = mysqli_fetch_all(mysqli_query($conn, 'SELECT id, name FROM levels ORDER BY id'), MYSQLI_ASSOC);
$departmentIds = array_map('intval', array_column($departments, 'id'));
$levelIds      = array_map('intval', array_column($levels, 'id'));

// ค่าเริ่มต้นของฟอร์ม = ค่าเดิมในฐานข้อมูล (แปลง id เป็น int เพื่อเทียบ === ใน dropdown ได้)
$full_name     = $user['full_name'];
$email         = $user['email'];
$role          = $user['role'];
$position      = $user['position'] ?? '';
$department_id = $user['department_id'] !== null ? (int) $user['department_id'] : null;
$level_id      = $user['level_id'] !== null ? (int) $user['level_id'] : null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';
    $position  = trim($_POST['position'] ?? '');
    $department_id = ($_POST['department_id'] ?? '') !== '' ? (int) $_POST['department_id'] : null;
    $level_id      = ($_POST['level_id'] ?? '') !== '' ? (int) $_POST['level_id'] : null;

    if ($full_name === '') {
        $error = 'กรุณากรอกชื่อ-นามสกุล';
    } elseif (mb_strlen($full_name) > 150) {
        $error = 'ชื่อ-นามสกุลยาวเกิน 150 ตัวอักษร';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif ($password !== '' && strlen($password) < 8) {
        // รหัสผ่านไม่บังคับ แต่ถ้ากรอกต้องยาวพอ
        $error = 'รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร';
    } elseif (!array_key_exists($role, $roles)) {
        $error = 'กรุณาเลือกบทบาท';
    } elseif ($id === (int) $me['id'] && $role !== 'admin') {
        // กันผู้ดูแลระบบลดสิทธิ์ตัวเองจนเข้าหน้านี้ไม่ได้อีก
        $error = 'ไม่สามารถเปลี่ยนบทบาทของตัวเองได้';
    } elseif ($department_id !== null && !in_array($department_id, $departmentIds, true)) {
        $error = 'ไม่พบแผนกที่เลือก';
    } elseif ($level_id !== null && !in_array($level_id, $levelIds, true)) {
        $error = 'ไม่พบระดับชั้นที่เลือก';
    } elseif (mb_strlen($position) > 100) {
        $error = 'ตำแหน่งยาวเกิน 100 ตัวอักษร';
    } else {
        // อีเมลซ้ำกับ "คนอื่น" หรือไม่ (ไม่นับตัวเอง)
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? AND id <> ?');
        mysqli_stmt_bind_param($stmt, 'si', $email, $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $error = 'อีเมลนี้มีผู้ใช้คนอื่นใช้แล้ว';
        }
    }

    if ($error === '') {
        $positionValue = $position !== '' ? $position : null;

        try {
            if ($password !== '') {
                // กรอกรหัสใหม่ → อัปเดต hash ด้วย
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn,
                    'UPDATE users SET email = ?, password_hash = ?, full_name = ?, role = ?,
                            department_id = ?, level_id = ?, position = ?
                     WHERE id = ?');
                mysqli_stmt_bind_param($stmt, 'ssssiisi',
                    $email, $password_hash, $full_name, $role, $department_id, $level_id, $positionValue, $id);
            } else {
                // เว้นว่าง → ไม่แตะ password_hash เดิม (ไม่งั้นจะเข้าระบบไม่ได้)
                $stmt = mysqli_prepare($conn,
                    'UPDATE users SET email = ?, full_name = ?, role = ?,
                            department_id = ?, level_id = ?, position = ?
                     WHERE id = ?');
                mysqli_stmt_bind_param($stmt, 'sssiisi',
                    $email, $full_name, $role, $department_id, $level_id, $positionValue, $id);
            }
            mysqli_stmt_execute($stmt);
            flash('success', 'บันทึกการแก้ไข "' . $full_name . '" เรียบร้อยแล้ว');
            header('Location: index.php');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $error = $ex->getCode() === 1062 ? 'อีเมลนี้มีผู้ใช้คนอื่นใช้แล้ว' : 'บันทึกไม่สำเร็จ กรุณาลองใหม่';
        }
    }
}

$isEdit = true;
$base = '../';
$pageTitle = 'แก้ไขผู้ใช้';
$activeMenu = 'users';
require '../includes/header.php';
?>
<h1 class="h4 fw-semibold mb-3">แก้ไขผู้ใช้ #<?php echo $id; ?></h1>
<?php require '_form.php'; ?>
<?php require '../includes/footer.php'; ?>
