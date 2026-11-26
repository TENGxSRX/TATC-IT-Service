<?php
require_once 'db.php';

// ออกจากระบบด้วย POST เท่านั้น (กันเว็บอื่นแอบส่งลิงก์ให้เราออกจากระบบ)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
csrf_check();

// ล้างข้อมูล session ทั้งหมด แล้วลบคุกกี้
$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
session_destroy();

// เริ่ม session ใหม่เพื่อแสดงข้อความหน้า login
session_start();
flash('success', 'ออกจากระบบเรียบร้อยแล้ว');
header('Location: login.php');
exit;
