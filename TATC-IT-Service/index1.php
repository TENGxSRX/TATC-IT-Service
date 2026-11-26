<?php
require_once 'db.php';
require_login();

$team = TEAM_NAME . ' กลุ่ม: ' . TEAM_GROUP;
$topic = APP_NAME;
$problem = 'การจัดการคลังยาและเวชภัณฑ์ห้องพยาบาล';
$members = ['ธงชัย จุลศรี (Developer A)', 'คุณากร ศรีเกิด (Developer B)'];

$pageTitle = 'ทีมผู้พัฒนา';
$activeMenu = 'team';
require 'includes/header.php';
?>
<div class="panel p-4">
  <h1 class="h4 fw-semibold mb-3"><?php echo e($topic); ?></h1>
  <p>ทีม: <?php echo e($team); ?></p>
  <p>ปัญหาที่แก้: <?php echo e($problem); ?></p>
  <h2 class="h6 fw-semibold">สมาชิก</h2>
  <ul>
    <?php foreach ($members as $m): ?>
      <li><?php echo e($m); ?></li>
    <?php endforeach; ?>
  </ul>
  <p class="text-secondary mb-0">อัปเดตล่าสุด: <?php echo date('d/m/Y H:i'); ?></p>
</div>
<?php require 'includes/footer.php'; ?>
