<?php
// ส่วนหัวที่ใช้ร่วมกันทุกหน้า
// ก่อน include ให้กำหนด: $pageTitle (ชื่อหน้า), $activeMenu (คีย์เมนู), $base ('' หรือ '../')
require_once __DIR__ . '/../db.php';

$base = $base ?? '';
$activeMenu = $activeMenu ?? '';
$pendingRequests = $pendingRequests ?? 0;

$currentUser = current_user();

// เมนู Sidebar ('roles' = บทบาทที่เห็นเมนูนี้, ไม่ระบุ = ทุกคนที่ล็อกอิน)
$menu = [
    'dashboard' => ['icon' => 'bi-grid-1x2',     'label' => 'แดชบอร์ด',        'href' => 'index.php'],
    'users'     => ['icon' => 'bi-people',       'label' => 'จัดการผู้ใช้',     'href' => 'users/index.php', 'roles' => ['admin']],
    'about'     => ['icon' => 'bi-info-circle',  'label' => 'เกี่ยวกับระบบ',    'href' => 'about.php'],
    'team'      => ['icon' => 'bi-person-badge', 'label' => 'ทีมผู้พัฒนา',      'href' => 'index1.php'],
    'server'    => ['icon' => 'bi-hdd-network',  'label' => 'ข้อมูลเซิร์ฟเวอร์', 'href' => 'server.php', 'roles' => ['admin']],
];
$menu = array_filter($menu, function ($item) use ($currentUser) {
    return empty($item['roles']) || ($currentUser && in_array($currentUser['role'], $item['roles'], true));
});
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle ?? ''); ?> · <?php echo e(APP_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="<?php echo e($base); ?>assets/css/style.css" rel="stylesheet">
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
    <div class="d-flex align-items-start justify-content-between">
      <a class="brand" href="<?php echo e($base); ?>index.php" id="sidebarLabel">
        <span class="brand-logo"><i class="bi bi-heart-pulse"></i></span>
        <span class="brand-name"><?php echo e(APP_NAME); ?></span>
      </a>
      <button type="button" class="btn-close btn-close-white d-lg-none m-3" data-bs-dismiss="offcanvas"
        data-bs-target="#sidebar" aria-label="ปิดเมนู"></button>
    </div>

    <div class="menu-title">เมนูหลัก</div>
    <nav>
      <?php foreach ($menu as $key => $item): $isActive = ($key === $activeMenu); ?>
        <a class="side-link<?php echo $isActive ? ' active' : ''; ?>" href="<?php echo e($base . $item['href']); ?>"
          <?php echo $isActive ? 'aria-current="page"' : ''; ?>>
          <i class="bi <?php echo e($item['icon']); ?>"></i>
          <span><?php echo e($item['label']); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <div class="d-flex align-items-center gap-3 px-3 px-lg-4 py-3">
        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
          aria-controls="sidebar" aria-label="เปิดเมนู">
          <i class="bi bi-list fs-5"></i>
        </button>
        <span class="fw-medium d-none d-md-inline"><?php echo e($pageTitle ?? ''); ?></span>

        <div class="ms-auto d-flex align-items-center gap-2">
          <button class="icon-btn" type="button" aria-label="การแจ้งเตือน">
            <i class="bi bi-bell"></i>
            <?php if ($pendingRequests > 0): ?><span class="dot"></span><?php endif; ?>
          </button>
          <?php if ($currentUser): ?>
            <div class="dropdown">
              <button class="btn d-flex align-items-center gap-2 p-1 border-0" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar"><?php echo e(mb_substr($currentUser['full_name'], 0, 1)); ?></span>
                <span class="text-start d-none d-sm-block lh-sm">
                  <span class="d-block fw-medium"><?php echo e($currentUser['full_name']); ?></span>
                  <small class="text-secondary"><?php echo e($roles[$currentUser['role']] ?? $currentUser['role']); ?></small>
                </span>
                <i class="bi bi-chevron-down small text-secondary"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 p-2">
                <div class="px-2 pb-2 small text-secondary"><?php echo e($currentUser['email']); ?></div>
                <form action="<?php echo e($base); ?>logout.php" method="post">
                  <?php echo csrf_field(); ?>
                  <button type="submit" class="dropdown-item rounded text-danger">
                    <i class="bi bi-box-arrow-left me-2"></i>ออกจากระบบ
                  </button>
                </form>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <main class="p-3 p-lg-4">
      <?php if ($flash = get_flash()): ?>
        <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible fade show" role="alert">
          <?php echo e($flash['message']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>
        </div>
      <?php endif; ?>
