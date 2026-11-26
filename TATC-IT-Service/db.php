<?php
// เชื่อมต่อฐานข้อมูล + ฟังก์ชันช่วยที่ใช้ร่วมกันทุกหน้าที่ต้องใช้ฐานข้อมูล
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    // คุกกี้ session: JavaScript อ่านไม่ได้ (httponly) และไม่ถูกส่งจากเว็บอื่น (SameSite)
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ที่อยู่หลักของเว็บ เช่น /TATC-IT-Service/ (ใช้ตอน redirect จากหน้าในโฟลเดอร์ย่อย)
$docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appDir  = str_replace('\\', '/', __DIR__);
define('BASE_URL', ($docRoot !== '' && stripos($appDir, $docRoot) === 0)
    ? rtrim(substr($appDir, strlen($docRoot)), '/') . '/'
    : '/');

// ให้ mysqli โยน exception เมื่อเกิด error (จะได้จับ error 1062 / 1451 / 1452 ได้)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="th"><head><meta charset="UTF-8"><title>เชื่อมต่อฐานข้อมูลไม่ได้</title></head>';
    echo '<body style="font-family:sans-serif;padding:2rem;max-width:640px">';
    echo '<h1>เชื่อมต่อฐานข้อมูลไม่ได้</h1>';
    echo '<p>ตรวจสอบว่าเปิด MySQL ใน XAMPP Control Panel แล้ว และนำเข้าไฟล์ <code>database/medstock_db.sql</code> ผ่าน phpMyAdmin แล้ว</p>';
    echo '</body></html>';
    exit;
}

// ---------- ข้อความแจ้งเตือนหลัง redirect (flash message) ----------
function flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash()
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------- ป้องกัน CSRF (ฟอร์มต้องมาจากเว็บเราเท่านั้น) ----------
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check()
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('คำขอไม่ถูกต้อง (CSRF token ไม่ตรง) กรุณากลับไปโหลดหน้าใหม่');
    }
}

// ---------- ระบบเข้าสู่ระบบ ----------

// ผู้ใช้ที่ล็อกอินอยู่ (โหลดจากฐานข้อมูลทุกครั้ง: ถ้าถูกลบหรือเปลี่ยนบทบาท จะมีผลทันที)
function current_user()
{
    global $conn;
    static $user = false;

    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = mysqli_prepare($conn, 'SELECT id, email, full_name, role FROM users WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$user) {
                unset($_SESSION['user_id']);   // บัญชีถูกลบไปแล้ว
                $user = null;
            }
        }
    }
    return $user;
}

// เรียกที่ต้นหน้าที่ต้องล็อกอินก่อน ถ้าส่ง $allowedRoles มา จะจำกัดเฉพาะบทบาทนั้น
function require_login(array $allowedRoles = [])
{
    $user = current_user();

    if (!$user) {
        // จำหน้าที่ตั้งใจจะเข้า เพื่อพากลับมาหลังล็อกอิน
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        flash('warning', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }

    if ($allowedRoles && !in_array($user['role'], $allowedRoles, true)) {
        flash('danger', 'คุณไม่มีสิทธิ์เข้าหน้านั้น');
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }

    return $user;
}
