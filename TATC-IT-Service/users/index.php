<?php
require_once '../db.php';
$me = require_login(['admin']);  // จัดการผู้ใช้ได้เฉพาะผู้ดูแลระบบ

// ---------- กรองตามบทบาทด้วย GET (อ่านข้อมูลอย่างเดียว บุ๊กมาร์กได้) ----------
$role = $_GET['role'] ?? '';
if (!array_key_exists($role, $roles)) {
    $role = '';                       // ค่าแปลก ๆ เช่น ?role=boss → แสดงทั้งหมด ไม่พัง
}

// LEFT JOIN เพื่อไม่ให้ผู้ใช้ที่ไม่มีแผนก/ระดับชั้น (NULL) หายไปจากรายการ
// ตั้งชื่อเล่น AS เพราะทั้งสองตารางมีคอลัมน์ name เหมือนกัน
$sql = "SELECT u.id, u.full_name, u.email, u.role, u.position,
               d.name AS department_name,
               l.name AS level_name
        FROM users u
        LEFT JOIN departments d ON d.id = u.department_id
        LEFT JOIN levels l ON l.id = u.level_id";

if ($role !== '') {
    $stmt = mysqli_prepare($conn, $sql . ' WHERE u.role = ? ORDER BY u.id');
    mysqli_stmt_bind_param($stmt, 's', $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql . ' ORDER BY u.id');
}
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

$roleBadge = [
    'admin'   => 'badge-soft-coral',
    'nurse'   => 'badge-soft-mint',
    'teacher' => 'badge-soft-blue',
    'student' => 'badge-soft-muted',
];

$base = '../';
$pageTitle = 'จัดการผู้ใช้';
$activeMenu = 'users';
require '../includes/header.php';
?>
<div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
  <div>
    <h1 class="h4 fw-semibold mb-1">จัดการผู้ใช้</h1>
    <p class="text-secondary mb-0">ทั้งหมด <?php echo count($users); ?> คน
      <?php echo $role !== '' ? '· บทบาท ' . e($roles[$role]) : ''; ?></p>
  </div>
  <a href="create.php" class="btn btn-brand px-3"><i class="bi bi-person-plus me-1"></i> เพิ่มผู้ใช้</a>
</div>

<!-- ปุ่มกรองตามบทบาท (ลิงก์ GET) -->
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="index.php" class="filter-pill<?php echo $role === '' ? ' active' : ''; ?>">ทั้งหมด</a>
  <?php foreach ($roles as $key => $label): ?>
    <a href="index.php?role=<?php echo e($key); ?>" class="filter-pill<?php echo $role === $key ? ' active' : ''; ?>">
      <?php echo e($label); ?>
    </a>
  <?php endforeach; ?>
</div>

<div class="panel">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th class="ps-4">#</th>
          <th>ชื่อ-นามสกุล</th>
          <th>อีเมล</th>
          <th>บทบาท</th>
          <th>แผนก</th>
          <th>ระดับชั้น</th>
          <th>ตำแหน่ง</th>
          <th class="pe-4 text-end">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$users): ?>
          <tr><td colspan="8" class="text-center text-secondary py-4">ไม่พบผู้ใช้</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="ps-4 text-secondary"><?php echo (int) $u['id']; ?></td>
            <td class="fw-medium"><?php echo e($u['full_name']); ?></td>
            <td><?php echo e($u['email']); ?></td>
            <td><span class="badge <?php echo $roleBadge[$u['role']]; ?>"><?php echo e($roles[$u['role']]); ?></span></td>
            <td><?php echo e($u['department_name'] ?? '-'); ?></td>
            <td><?php echo e($u['level_name'] ?? '-'); ?></td>
            <td><?php echo e($u['position'] ?? '-'); ?></td>
            <td class="pe-4 text-end">
              <a href="edit.php?id=<?php echo (int) $u['id']; ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> แก้ไข
              </a>
              <!-- ลบต้องใช้ POST เท่านั้น (ไม่แสดงปุ่มลบที่บัญชีของตัวเอง) -->
              <?php if ((int) $u['id'] !== (int) $me['id']): ?>
              <form action="delete.php" method="post" class="d-inline"
                onsubmit="return confirm('ยืนยันลบผู้ใช้ <?php echo e(addslashes($u['full_name'])); ?> ?');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> ลบ</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require '../includes/footer.php'; ?>
