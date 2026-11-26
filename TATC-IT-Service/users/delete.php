<?php
require_once '../db.php';

// ลบได้ด้วย POST เท่านั้น (ถ้าใช้ GET แค่เปิดลิงก์ก็ลบได้ อันตราย)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('ต้องลบผ่านปุ่มในหน้ารายการผู้ใช้เท่านั้น');
}

$me = require_login(['admin']);
csrf_check();

$id = (int) ($_POST['id'] ?? 0);

// ห้ามลบบัญชีตัวเอง (ไม่งั้นอาจไม่เหลือผู้ดูแลระบบเลย)
if ($id === (int) $me['id']) {
    flash('warning', 'ไม่สามารถลบบัญชีของตัวเองได้');
    header('Location: index.php');
    exit;
}

try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        flash('success', 'ลบผู้ใช้เรียบร้อยแล้ว');
    } else {
        flash('warning', 'ไม่พบผู้ใช้ที่ต้องการลบ');
    }
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() === 1451) {
        // มีข้อมูลในตารางอื่นอ้างถึงผู้ใช้คนนี้ (เช่น คำขอเบิก) จึงลบไม่ได้
        flash('danger', 'ลบไม่ได้ เพราะผู้ใช้นี้มีประวัติคำขอเบิกเวชภัณฑ์ในระบบ');
    } else {
        flash('danger', 'ลบไม่สำเร็จ กรุณาลองใหม่');
    }
}

header('Location: index.php');
exit;
