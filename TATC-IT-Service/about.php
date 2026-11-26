<?php
require_once 'db.php';
require_login();

// อาร์เรย์เก็บเป้าหมายของระบบ MedStock อย่างน้อย 3 ข้อ
$goals = [
    'บริหารจัดการคลังยาและเวชภัณฑ์ห้องพยาบาลอย่างมีประสิทธิภาพ',
    'แจ้งเตือนเมื่อรายการยาใกล้หมดสต็อกหรือใกล้หมดอายุ',
    'ติดตามและควบคุมงบประมาณการเบิกจ่ายยาในระบบ',
];

$pageTitle = 'เกี่ยวกับระบบ';
$activeMenu = 'about';
require 'includes/header.php';
?>
<div class="panel p-4">
  <h1 class="h4 fw-semibold mb-3">เกี่ยวกับระบบ <?php echo e(APP_NAME); ?></h1>

  <!-- แสดงจำนวนข้อด้วย count($goals) -->
  <h2 class="h6 fw-semibold">เป้าหมายของระบบ (ทั้งหมด <?php echo count($goals); ?> ข้อ)</h2>

  <!-- แสดงรายการเป้าหมายด้วย <ol> -->
  <ol class="mb-0">
    <?php foreach ($goals as $goal): ?>
      <li><?php echo e($goal); ?></li>
    <?php endforeach; ?>
  </ol>
</div>
<?php require 'includes/footer.php'; ?>
