<?php
require_once 'db.php';
require_login(['admin']);        // ข้อมูลเซิร์ฟเวอร์ เฉพาะผู้ดูแลระบบ

$pageTitle = 'ข้อมูลเซิร์ฟเวอร์';
$activeMenu = 'server';
require 'includes/header.php';
?>
<div class="panel">
  <div class="p-3 px-4">
    <h1 class="h5 fw-semibold mb-0">เซิร์ฟเวอร์ของทีม</h1>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th class="ps-4">รายการ</th><th>ค่า</th></tr>
      </thead>
      <tbody>
        <tr><td class="ps-4">เวอร์ชัน PHP</td><td><?php echo e(PHP_VERSION); ?></td></tr>
        <tr><td class="ps-4">ซอฟต์แวร์เว็บเซิร์ฟเวอร์</td><td><?php echo e($_SERVER['SERVER_SOFTWARE'] ?? '-'); ?></td></tr>
        <tr><td class="ps-4">ชื่อเครื่อง:พอร์ต</td><td><?php echo e($_SERVER['HTTP_HOST'] ?? '-'); ?></td></tr>
        <tr><td class="ps-4">Timezone</td><td><?php echo e(date_default_timezone_get()); ?></td></tr>
        <tr><td class="ps-4">เวลาบนเซิร์ฟเวอร์</td><td><?php echo date('d/m/Y H:i:s'); ?></td></tr>
      </tbody>
    </table>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
