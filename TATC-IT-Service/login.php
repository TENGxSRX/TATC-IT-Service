<?php
require_once 'db.php';

// ล็อกอินอยู่แล้ว → ไปแดชบอร์ดเลย
if (current_user()) {
    header('Location: index.php');
    exit;
}

const MAX_ATTEMPTS = 5;      // กรอกผิดได้กี่ครั้ง
const LOCK_SECONDS = 60;     // ถ้าผิดครบ ต้องรอกี่วินาที

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $attempts  = $_SESSION['login_attempts'] ?? 0;
    $lockUntil = $_SESSION['login_lock_until'] ?? 0;

    if ($lockUntil > time()) {
        $error = 'กรอกผิดหลายครั้งเกินไป กรุณารอ ' . ($lockUntil - time()) . ' วินาที';
    } elseif ($email === '' || $password === '') {
        $error = 'กรุณากรอกอีเมลและรหัสผ่าน';
    } else {
        // 1) ค้นผู้ใช้จากอีเมล
        $stmt = mysqli_prepare($conn, 'SELECT id, password_hash FROM users WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // 2) ตรวจรหัสผ่านกับ hash ที่เก็บไว้
        if ($user && password_verify($password, $user['password_hash'])) {
            // ถ้า PHP แนะนำอัลกอริทึม/cost ใหม่ ให้อัปเดต hash อัตโนมัติ
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = mysqli_prepare($conn, 'UPDATE users SET password_hash = ? WHERE id = ?');
                mysqli_stmt_bind_param($up, 'si', $newHash, $user['id']);
                mysqli_stmt_execute($up);
            }

            // 3) สร้าง session id ใหม่ กันการขโมย session (session fixation)
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            unset($_SESSION['login_attempts'], $_SESSION['login_lock_until'], $_SESSION['csrf']);

            // กลับไปหน้าที่ตั้งใจจะเข้า (เฉพาะหน้าในเว็บเราเท่านั้น)
            $next = $_SESSION['after_login'] ?? '';
            unset($_SESSION['after_login']);
            if (!is_string($next) || strpos($next, BASE_URL) !== 0 || strpos($next, '//') !== false) {
                $next = 'index.php';
            }
            header('Location: ' . $next);
            exit;
        }

        // ไม่บอกว่าผิดที่อีเมลหรือรหัสผ่าน (ไม่ให้คนร้ายรู้ว่าอีเมลนี้มีในระบบ)
        $attempts++;
        $_SESSION['login_attempts'] = $attempts;
        if ($attempts >= MAX_ATTEMPTS) {
            $_SESSION['login_lock_until'] = time() + LOCK_SECONDS;
            $_SESSION['login_attempts'] = 0;
            $error = 'กรอกผิดครบ ' . MAX_ATTEMPTS . ' ครั้ง กรุณารอ ' . LOCK_SECONDS . ' วินาที';
        } else {
            $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง (เหลืออีก ' . (MAX_ATTEMPTS - $attempts) . ' ครั้ง)';
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ · <?php echo e(APP_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="d-flex align-items-center min-vh-100 py-4">
  <main class="container" style="max-width: 420px;">
    <div class="text-center mb-4">
      <span class="brand-logo mx-auto mb-3" style="width:56px;height:56px;font-size:1.6rem;"><i class="bi bi-heart-pulse"></i></span>
      <h1 class="h4 fw-semibold mb-1"><?php echo e(APP_NAME); ?></h1>
      <p class="text-secondary mb-0">เข้าสู่ระบบเพื่อใช้งาน</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="post" class="panel p-4">
      <?php echo csrf_field(); ?>
      <div class="mb-3">
        <label class="form-label" for="email">อีเมล</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus
          autocomplete="username" value="<?php echo e($email); ?>">
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">รหัสผ่าน</label>
        <input type="password" class="form-control" id="password" name="password" required
          autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-brand w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
      </button>
    </form>
  </main>
</body>

</html>
