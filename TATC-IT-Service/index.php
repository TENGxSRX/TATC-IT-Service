<?php
require_once 'config.php';

// ==========================================
// ประกาศตัวแปรและค่าคงที่ตามตารางข้อกำหนดใบงาน
// ==========================================
$totalMedicines   = 1000;                  // จำนวนยาทั้งหมดในระบบ (int)[cite: 3]
$dispensed        = 250;                   // จำนวนยาที่เบิกจ่ายไปแล้ว (int)[cite: 3]
$nearExpiring     = 15;                    // จำนวนยาที่ใกล้หมดอายุ (int)[cite: 3]
$lastItem         = 'Paracetamol 500mg';   // รายการยาที่เบิกล่าสุด (string)[cite: 3]
$isOpen           = true;                  // สถานะห้องพยาบาล เปิด/ปิด (bool)[cite: 3]

$scannedQrCode    = 'MED-0012';            // รหัสเวชภัณฑ์จาก QR Code (string)[cite: 3]
$mostUsedItem     = 'พาราเซตามอล';          // รายการยาที่ใช้บ่อยที่สุด (string)[cite: 3]
$requestStatus    = 'รออนุมัติ';            // สถานะคำขอเบิก (string)[cite: 3]
$pendingRequests  = 3;                     // จำนวนคำขอเบิกที่รออนุมัติ (int)[cite: 3]

// ตัวอย่างรายการคำขอเบิกเวชภัณฑ์
$requests = [
  ['code' => 'REQ-012', 'item' => 'พาราเซตามอล 500mg (10 แผง)', 'requester' => 'นรีกานต์ (ปวส.1)', 'date' => '-1 hours', 'status' => 'pending'],
  ['code' => 'REQ-011', 'item' => 'แอลกอฮอล์ 70% 450ml (2 ขวด)', 'requester' => 'อ.สมชาย (หมวดพละ)', 'date' => '-3 hours', 'status' => 'pending'],
  ['code' => 'REQ-010', 'item' => 'พลาสเตอร์ยา (1 กล่อง)', 'requester' => 'วิภาวี (ปวช.2)', 'date' => '-1 days', 'status' => 'approved'],
  ['code' => 'REQ-009', 'item' => 'น้ำเกลือล้างแผล (1 ขวด)', 'requester' => 'ธนกร (ช่างยนต์)', 'date' => '-1 days', 'status' => 'rejected'],
  ['code' => 'REQ-008', 'item' => 'ผ้าก๊อซแบบแผ่น (2 ซอง)', 'requester' => 'ปณิตา (บัญชี)', 'date' => '-2 days', 'status' => 'approved'],
];

foreach ($requests as &$req) {
  $req['time_stamp'] = strtotime($req['date']);
}
unset($req);

// ข้อมูลอัปเดตการ์ด 4 ใบให้ตรงกับตัวแปรตารางใบงาน
$stats = [
  ['label' => 'จำนวนยาทั้งหมดในระบบ', 'value' => number_format($totalMedicines), 'unit' => 'รายการ', 'icon' => 'bi-box2-heart', 'tone' => 'blue'],
  ['label' => 'เบิกจ่ายไปแล้ว', 'value' => number_format($dispensed), 'unit' => 'รายการ', 'icon' => 'bi-box-arrow-up-right', 'tone' => 'sky'],
  ['label' => 'ยาใกล้หมดอายุ', 'value' => $nearExpiring, 'unit' => 'รายการ', 'icon' => 'bi-calendar-x', 'tone' => 'coral'],
  ['label' => 'คำขอรออนุมัติ', 'value' => $pendingRequests, 'unit' => 'รายการ', 'icon' => 'bi-clipboard-data', 'tone' => 'mint'],
];

// สีและข้อความสถานะคำขอเบิก
$statusBadge = [
  'pending'  => ['รออนุมัติ', 'badge-soft-warning'],
  'approved' => ['อนุมัติแล้ว', 'badge-soft-mint'],
  'rejected' => ['ไม่อนุมัติ', 'badge-soft-muted'],
];

// เมนู Sidebar
$menu = [
  ['icon' => 'bi-grid-1x2', 'label' => 'แดชบอร์ด', 'href' => 'index.php', 'active' => true],
  ['icon' => 'bi-box2-heart', 'label' => 'คลังเวชภัณฑ์', 'href' => '#'],
  ['icon' => 'bi-qr-code-scan', 'label' => 'สแกน QR อุปกรณ์', 'href' => '#'],
  ['icon' => 'bi-clipboard-check', 'label' => 'คำขอเบิก', 'href' => '#'],
  ['icon' => 'bi-bar-chart-line', 'label' => 'รายงานสรุป', 'href' => '#'],
  ['icon' => 'bi-info-circle', 'label' => 'เกี่ยวกับ', 'href' => 'about.php'],
];

function e($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แดชบอร์ด · <?php echo e(APP_NAME); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --pp-bg: #f4f8fd;
      --pp-surface: #ffffff;
      --pp-sidebar: #1e2f4d;
      --pp-sidebar-text: #a9bdd6;
      --pp-sidebar-muted: #6f86a6;
      --pp-border: #dce8f5;
      --pp-text: #23364d;
      --pp-muted: #6b819b;
      --pp-primary: #4a8ddc;
      --pp-primary-soft: #d8eafc;
      --pp-sky: #5bb2e6;
      --pp-sky-soft: #ddf1fb;
      --pp-coral: #e87a7a;
      --pp-coral-soft: #fce3e3;
      --pp-mint: #45b894;
      --pp-mint-soft: #dcf4ec;
      --pp-warning: #eea83b;
      --pp-warning-soft: #fdf3df;
      --pp-gray-soft: #eef2f6;
      --sidebar-w: 250px;
    }

    body {
      font-family: 'Prompt', system-ui, sans-serif;
      background: var(--pp-bg);
      color: var(--pp-text);
    }

    /* ---------- Sidebar ---------- */
    .sidebar {
      width: var(--sidebar-w);
      background-color: var(--pp-sidebar) !important;
      border-right: 0;
    }

    @media (min-width: 992px) {
      .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
      }

      .main {
        margin-left: var(--sidebar-w);
      }
    }

    .brand {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: 1.25rem 1.25rem 1rem;
      color: #fff;
      text-decoration: none;
    }

    .brand-logo {
      width: 40px;
      height: 40px;
      flex-shrink: 0;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: var(--pp-primary);
      color: #fff;
      font-size: 1.2rem;
    }

    .brand-name {
      font-weight: 600;
      font-size: .95rem;
      line-height: 1.3;
    }

    .menu-title {
      font-size: .72rem;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--pp-sidebar-muted);
      padding: 1rem 1.5rem .5rem;
    }

    .side-link {
      display: flex;
      align-items: center;
      gap: .75rem;
      margin: .15rem .75rem;
      padding: .65rem .9rem;
      border-radius: 10px;
      color: var(--pp-sidebar-text);
      text-decoration: none;
      font-size: .95rem;
      transition: background .15s, color .15s;
    }

    .side-link:hover {
      background: rgba(255, 255, 255, .06);
      color: #fff;
    }

    .side-link.active {
      background: var(--pp-primary);
      color: #fff;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(74, 141, 220, .35);
    }

    /* ---------- Topbar ---------- */
    .topbar {
      position: sticky;
      top: 0;
      z-index: 1020;
      background: rgba(255, 255, 255, .85);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid var(--pp-border);
    }

    .search {
      max-width: 320px;
    }

    .search .form-control,
    .search .input-group-text {
      background: var(--pp-bg);
      border-color: var(--pp-border);
    }

    .icon-btn {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      border: 1px solid var(--pp-border);
      background: var(--pp-surface);
      color: var(--pp-muted);
      position: relative;
    }

    .icon-btn .dot {
      position: absolute;
      top: 8px;
      right: 9px;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--pp-coral);
      border: 2px solid #fff;
    }

    .avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: grid;
      place-items: center;
      background: var(--pp-primary-soft);
      color: var(--pp-primary);
      font-weight: 600;
    }

    /* ---------- Cards ---------- */
    .panel {
      background: var(--pp-surface);
      border: 1px solid var(--pp-border);
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(35, 54, 77, .04);
    }

    .stat-card {
      padding: 1.25rem;
      height: 100%;
      transition: transform .15s, box-shadow .15s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(74, 141, 220, .15);
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: grid;
      place-items: center;
      font-size: 1.35rem;
    }

    .stat-label {
      color: var(--pp-muted);
      font-size: .9rem;
    }

    .stat-value {
      font-size: 1.9rem;
      font-weight: 600;
      line-height: 1.1;
      font-variant-numeric: tabular-nums;
    }

    .stat-unit {
      color: var(--pp-muted);
      font-size: .85rem;
    }

    .tone-blue { background: var(--pp-primary-soft); color: var(--pp-primary); }
    .tone-sky { background: var(--pp-sky-soft); color: #3f9fcf; }
    .tone-coral { background: var(--pp-coral-soft); color: var(--pp-coral); }
    .tone-mint { background: var(--pp-mint-soft); color: var(--pp-mint); }

    /* ---------- Table & Badges ---------- */
    .table {
      --bs-table-bg: transparent;
      margin: 0;
    }

    .table thead th {
      font-size: .8rem;
      font-weight: 500;
      color: var(--pp-muted);
      background: var(--pp-bg);
      border-bottom: 0;
      white-space: nowrap;
    }

    .table td {
      border-color: var(--pp-border);
      vertical-align: middle;
      white-space: nowrap;
    }

    .badge-soft-blue { background: var(--pp-primary-soft); color: #3b7fc8; font-weight: 500; padding: .4em .75em; border-radius: 999px; }
    .badge-soft-coral { background: var(--pp-coral-soft); color: #c95f53; font-weight: 500; padding: .4em .75em; border-radius: 999px; }
    .badge-soft-warning { background: var(--pp-warning-soft); color: #c4831c; font-weight: 500; padding: .4em .75em; border-radius: 999px; }
    .badge-soft-mint { background: var(--pp-mint-soft); color: #3c9a7c; font-weight: 500; padding: .4em .75em; border-radius: 999px; }
    .badge-soft-muted { background: var(--pp-gray-soft); color: #6b819b; font-weight: 500; padding: .4em .75em; border-radius: 999px; }

    .qr-button {
      background: var(--pp-text);
      color: white;
      border-radius: 12px;
      padding: 1.2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      transition: .2s;
    }

    .qr-button:hover {
      background: #131e2b;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .qr-icon {
      font-size: 2rem;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
    <div class="d-flex align-items-start justify-content-between">
      <a class="brand" href="index.php" id="sidebarLabel">
        <span class="brand-logo"><i class="bi bi-heart-pulse"></i></span>
        <span class="brand-name"><?php echo e(APP_NAME); ?></span>
      </a>
      <button type="button" class="btn-close btn-close-white d-lg-none m-3" data-bs-dismiss="offcanvas"
        data-bs-target="#sidebar" aria-label="Close"></button>
    </div>

    <div class="menu-title">เมนูหลัก</div>
    <nav>
      <?php foreach ($menu as $item): ?>
        <a class="side-link<?php echo !empty($item['active']) ? ' active' : ''; ?>" href="<?php echo e($item['href']); ?>"
          <?php echo !empty($item['active']) ? 'aria-current="page"' : ''; ?>>
          <i class="bi <?php echo e($item['icon']); ?>"></i>
          <span><?php echo e($item['label']); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="menu-title">ระบบ</div>
    <a class="side-link" href="#"><i class="bi bi-gear"></i><span>ตั้งค่าผู้ใช้งาน</span></a>
    <a class="side-link" href="#"><i class="bi bi-box-arrow-left"></i><span>ออกจากระบบ</span></a>
  </aside>

  <div class="main">
    <!-- Topbar -->
    <header class="topbar">
      <div class="d-flex align-items-center gap-3 px-3 px-lg-4 py-3">
        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
          aria-controls="sidebar" aria-label="เปิดเมนู">
          <i class="bi bi-list fs-5"></i>
        </button>

        <div class="input-group search d-none d-md-flex">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" class="form-control" placeholder="ค้นหา รหัสยา / ชื่อเวชภัณฑ์...">
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
          <button class="icon-btn" type="button" aria-label="การแจ้งเตือน">
            <i class="bi bi-bell"></i>
            <?php if ($pendingRequests > 0): ?><span class="dot"></span><?php endif; ?>
          </button>
          <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 p-1 border-0" type="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <span class="avatar">N</span>
              <span class="text-start d-none d-sm-block lh-sm">
                <span class="d-block fw-medium">Nurse Jane</span>
                <small class="text-secondary">พยาบาลวิชาชีพ</small>
              </span>
              <i class="bi bi-chevron-down small text-secondary"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>ตั้งค่า</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-left me-2"></i>ออกจากระบบ</a></li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <main class="p-3 p-lg-4">
      <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-4">
        <div>
          <h1 class="h4 fw-semibold mb-1">แดชบอร์ดห้องพยาบาล</h1>
          <p class="text-secondary mb-0">
            สถานะห้องพยาบาล: 
            <?php if ($isOpen): ?>
              <span class="badge badge-soft-mint"><i class="bi bi-door-open me-1"></i>เปิดทำการ</span>
            <?php else: ?>
              <span class="badge badge-soft-coral"><i class="bi bi-door-closed me-1"></i>ปิดทำการ</span>
            <?php endif; ?>
            · ข้อมูล ณ วันที่ <?php echo date('d/m/Y'); ?>
          </p>
        </div>
        <a href="#" class="btn btn-primary px-3"
          style="background: var(--pp-primary); border-color: var(--pp-primary);">
          <i class="bi bi-plus-lg me-1"></i> สร้างคำขอเบิกเวชภัณฑ์
        </a>
      </div>

      <!-- การ์ดสรุป 4 ใบ -->
      <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="panel stat-card d-flex align-items-center gap-3">
              <div class="stat-icon tone-<?php echo e($s['tone']); ?>"><i class="bi <?php echo e($s['icon']); ?>"></i></div>
              <div>
                <div class="stat-label"><?php echo e($s['label']); ?></div>
                <div class="stat-value"><?php echo e($s['value']); ?></div>
                <div class="stat-unit"><?php echo e($s['unit']); ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <!-- รายการคำขอเบิกล่าสุด -->
        <div class="col-12 col-xl-8">
          <div class="panel h-100">
            <div class="d-flex align-items-center justify-content-between p-3 px-4">
              <h2 class="h6 fw-semibold mb-0">รายการคำขอเบิก/จ่าย ล่าสุด</h2>
              <a href="#" class="small text-decoration-none" style="color: var(--pp-primary);">ดูประวัติทั้งหมด</a>
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th class="ps-4">เลขที่อ้างอิง</th>
                    <th>รายการเวชภัณฑ์</th>
                    <th>ผู้เบิก / ห้อง</th>
                    <th>เวลาส่งคำขอ</th>
                    <th class="pe-4">สถานะ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($requests as $req): [$label, $cls] = $statusBadge[$req['status']]; ?>
                    <tr>
                      <td class="ps-4 text-secondary"><?php echo e($req['code']); ?></td>
                      <td class="fw-medium"><?php echo e($req['item']); ?></td>
                      <td><?php echo e($req['requester']); ?></td>
                      <td><?php echo date('d/m/Y H:i', $req['time_stamp']); ?></td>
                      <td class="pe-4">
                        <span class="badge <?php echo $cls; ?>"><?php echo $label; ?></span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- กล่องข้อมูลจากตารางใบงาน & QR Code -->
        <div class="col-12 col-xl-4">

          <!-- ปุ่ม QR Code Scanner แสดงรหัสสแกนล่าสุด -->
          <a href="#" class="qr-button mb-3">
            <i class="bi bi-qr-code-scan qr-icon"></i>
            <div>
              <div class="fw-semibold">สแกน QR Code (ล่าสุด: <?php echo e($scannedQrCode); ?>)</div>
              <small class="text-white-50">เปิดกล้องเพื่อเช็คข้อมูลยา / ตัดสต็อกทันที</small>
            </div>
          </a>

          <!-- สรุปข้อมูลตัวแปรสถิติในใบงาน -->
          <div class="panel p-4 mb-3">
            <h2 class="h6 fw-semibold mb-3">สรุปข้อมูลเวชภัณฑ์</h2>
            <ul class="list-unstyled mb-0 d-grid gap-2">
              <li class="d-flex justify-content-between align-items-center border-bottom pb-2">
                <span class="text-secondary small">รายการเบิกล่าสุด</span>
                <span class="fw-medium small"><?php echo e($lastItem); ?></span>
              </li>
              <li class="d-flex justify-content-between align-items-center border-bottom pb-2">
                <span class="text-secondary small">เวชภัณฑ์ที่ใช้บ่อยที่สุด</span>
                <span class="fw-medium small text-primary"><?php echo e($mostUsedItem); ?></span>
              </li>
              <li class="d-flex justify-content-between align-items-center border-bottom pb-2">
                <span class="text-secondary small">สถานะคำขอเบิกล่าสุด</span>
                <span class="badge badge-soft-warning"><?php echo e($requestStatus); ?></span>
              </li>
              <li class="d-flex justify-content-between align-items-center pt-1">
                <span class="text-secondary small">เกณฑ์เตือนยาใกล้หมดสต็อก</span>
                <span class="fw-semibold text-danger small">< <?php echo ALERT_LOW_STOCK; ?> รายการ</span>
              </li>
            </ul>
          </div>

          <div class="panel p-4">
            <h2 class="h6 fw-semibold mb-3">ระเบียบการเบิกจ่าย</h2>
            <ul class="list-unstyled mb-0 d-grid gap-3">
              <li class="d-flex gap-3 align-items-center">
                <span class="stat-icon tone-blue" style="width:40px;height:40px;font-size:1.1rem;"><i class="bi bi-clock-history"></i></span>
                <span class="small">ตัดสต็อกเมื่อ <strong>"อนุมัติแล้ว"</strong> เท่านั้น</span>
              </li>
              <li class="d-flex gap-3 align-items-center">
                <span class="stat-icon tone-coral" style="width:40px;height:40px;font-size:1.1rem;"><i class="bi bi-shield-exclamation"></i></span>
                <span class="small">ระบบจะแจ้งเตือนเมื่อยาหมดอายุล่วงหน้า <strong><?php echo ALERT_EXPIRY_DAYS; ?> วัน</strong></span>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>