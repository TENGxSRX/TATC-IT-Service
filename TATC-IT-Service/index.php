<?php
require_once 'db.php';
require_login();                 // ทุกบทบาทที่ล็อกอินแล้วเข้าได้

// ==========================================
// ประกาศตัวแปรตามตารางข้อกำหนดใบงาน
// ==========================================
$totalMedicines   = 1000;                  // จำนวนยาทั้งหมดในระบบ (int)
$dispensed        = 250;                   // จำนวนยาที่เบิกจ่ายไปแล้ว (int)
$nearExpiring     = 15;                    // จำนวนยาที่ใกล้หมดอายุ (int)
$lastItem         = 'Paracetamol 500mg';   // รายการยาที่เบิกล่าสุด (string)
$isOpen           = true;                  // สถานะห้องพยาบาล เปิด/ปิด (bool)
$scannedQrCode    = 'MED-0012';            // รหัสเวชภัณฑ์จาก QR Code (string)
$mostUsedItem     = 'พาราเซตามอล';          // รายการยาที่ใช้บ่อยที่สุด (string)

// ==========================================
// ข้อมูลคำขอเบิกจากฐานข้อมูล
// ==========================================
// LEFT JOIN แผนก/ระดับชั้น เพราะครูไม่มีระดับชั้น และบางคนไม่มีแผนก
$sql = "SELECT r.code, r.item, r.status, r.created_at,
               u.full_name, d.name AS department_name, l.name AS level_name
        FROM requests r
        JOIN users u ON u.id = r.requester_id
        LEFT JOIN departments d ON d.id = u.department_id
        LEFT JOIN levels l ON l.id = u.level_id
        ORDER BY r.created_at DESC
        LIMIT 5";
$requests = mysqli_fetch_all(mysqli_query($conn, $sql), MYSQLI_ASSOC);

$pendingRequests = (int) mysqli_fetch_row(
    mysqli_query($conn, "SELECT COUNT(*) FROM requests WHERE status = 'pending'")
)[0];

// สถานะคำขอเบิกล่าสุด (string)
$statusBadge = [
    'pending'  => ['รออนุมัติ', 'badge-soft-warning'],
    'approved' => ['อนุมัติแล้ว', 'badge-soft-mint'],
    'rejected' => ['ไม่อนุมัติ', 'badge-soft-muted'],
];
$requestStatus = $requests ? $statusBadge[$requests[0]['status']][0] : '-';

// การ์ดสรุป 4 ใบ
$stats = [
    ['label' => 'จำนวนยาทั้งหมดในระบบ', 'value' => number_format($totalMedicines), 'icon' => 'bi-box2-heart', 'tone' => 'blue'],
    ['label' => 'เบิกจ่ายไปแล้ว',       'value' => number_format($dispensed),      'icon' => 'bi-box-arrow-up-right', 'tone' => 'sky'],
    ['label' => 'ยาใกล้หมดอายุ',        'value' => $nearExpiring,                  'icon' => 'bi-calendar-x', 'tone' => 'coral'],
    ['label' => 'คำขอรออนุมัติ',        'value' => $pendingRequests,               'icon' => 'bi-clipboard-data', 'tone' => 'mint'],
];

$pageTitle = 'แดชบอร์ด';
$activeMenu = 'dashboard';
require 'includes/header.php';
?>
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
          <div class="stat-unit">รายการ</div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <!-- รายการคำขอเบิกล่าสุด -->
  <div class="col-12 col-xl-8">
    <div class="panel h-100">
      <div class="p-3 px-4">
        <h2 class="h6 fw-semibold mb-0">รายการคำขอเบิก/จ่าย ล่าสุด</h2>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="ps-4">เลขที่อ้างอิง</th>
              <th>รายการเวชภัณฑ์</th>
              <th>ผู้เบิก / แผนก</th>
              <th>เวลาส่งคำขอ</th>
              <th class="pe-4">สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$requests): ?>
              <tr><td colspan="5" class="text-center text-secondary py-4">ยังไม่มีคำขอเบิก</td></tr>
            <?php endif; ?>
            <?php foreach ($requests as $req): [$label, $cls] = $statusBadge[$req['status']]; ?>
              <tr>
                <td class="ps-4 text-secondary"><?php echo e($req['code']); ?></td>
                <td class="fw-medium"><?php echo e($req['item']); ?></td>
                <td>
                  <?php echo e($req['full_name']); ?>
                  <small class="text-secondary">
                    (<?php echo e(implode(' · ', array_filter([$req['level_name'], $req['department_name']])) ?: '-'); ?>)
                  </small>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($req['created_at'])); ?></td>
                <td class="pe-4"><span class="badge <?php echo $cls; ?>"><?php echo e($label); ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <!-- รหัส QR ที่สแกนล่าสุด -->
    <div class="qr-button mb-3">
      <i class="bi bi-qr-code-scan qr-icon"></i>
      <div>
        <div class="fw-semibold">QR Code ที่สแกนล่าสุด: <?php echo e($scannedQrCode); ?></div>
        <small class="text-white-50">ใช้เช็คข้อมูลยา / ตัดสต็อก</small>
      </div>
    </div>

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
          <span class="fw-semibold text-danger small">&lt; <?php echo ALERT_LOW_STOCK; ?> รายการ</span>
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
          <span class="small">แจ้งเตือนยาหมดอายุล่วงหน้า <strong><?php echo ALERT_EXPIRY_DAYS; ?> วัน</strong></span>
        </li>
        <li class="d-flex gap-3 align-items-center">
          <span class="stat-icon tone-mint" style="width:40px;height:40px;font-size:1.1rem;"><i class="bi bi-cash-coin"></i></span>
          <span class="small">แจ้งเตือนเมื่อใช้งบประมาณถึง <strong><?php echo ALERT_PERCENT; ?>%</strong></span>
        </li>
      </ul>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
