<?php
// ฟอร์มผู้ใช้ ใช้ร่วมกันระหว่าง create.php และ edit.php
// ต้องมีตัวแปร: $full_name, $email, $role, $department_id, $level_id, $position,
//               $departments, $levels, $roles, $isEdit, $error
if (!isset($isEdit)) {      // กันการเปิดไฟล์นี้ตรง ๆ
    http_response_code(404);
    exit;
}
?>
<?php if ($error !== ''): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?php echo e($error); ?></div>
<?php endif; ?>

<form method="post" class="panel p-4" novalidate>
  <?php echo csrf_field(); ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="full_name">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="full_name" name="full_name" maxlength="150" required
        value="<?php echo e($full_name); ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="email">อีเมล <span class="text-danger">*</span></label>
      <input type="email" class="form-control" id="email" name="email" maxlength="255" required
        value="<?php echo e($email); ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="password">
        รหัสผ่าน <?php echo $isEdit ? '' : '<span class="text-danger">*</span>'; ?>
      </label>
      <!-- ไม่แสดงรหัสเดิม เพราะระบบเก็บแค่ hash ดูย้อนกลับไม่ได้ -->
      <input type="password" class="form-control" id="password" name="password" minlength="8"
        autocomplete="new-password" <?php echo $isEdit ? '' : 'required'; ?>>
      <div class="form-text">
        <?php echo $isEdit ? 'เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยนรหัสผ่าน' : 'อย่างน้อย 8 ตัวอักษร'; ?>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="role">บทบาท <span class="text-danger">*</span></label>
      <!-- บทบาทมีค่าคงที่ ไม่ต้องดึงจากตาราง ใช้ array $roles -->
      <select class="form-select" id="role" name="role" required>
        <option value="">-- เลือกบทบาท --</option>
        <?php foreach ($roles as $key => $label): ?>
          <option value="<?php echo e($key); ?>" <?php echo $role === $key ? 'selected' : ''; ?>>
            <?php echo e($label); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="department_id">แผนกวิชา</label>
      <!-- dropdown จากตาราง departments: เห็นชื่อ แต่ส่ง id -->
      <select class="form-select" id="department_id" name="department_id">
        <option value="">-- ไม่ระบุ --</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?php echo (int) $d['id']; ?>" <?php echo $department_id === (int) $d['id'] ? 'selected' : ''; ?>>
            <?php echo e($d['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">ผู้ดูแลระบบ/พยาบาล ไม่ต้องระบุ</div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="level_id">ระดับชั้น</label>
      <select class="form-select" id="level_id" name="level_id">
        <option value="">-- ไม่ระบุ --</option>
        <?php foreach ($levels as $l): ?>
          <option value="<?php echo (int) $l['id']; ?>" <?php echo $level_id === (int) $l['id'] ? 'selected' : ''; ?>>
            <?php echo e($l['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="form-text">เฉพาะนักศึกษา</div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="position">ตำแหน่ง</label>
      <input type="text" class="form-control" id="position" name="position" maxlength="100"
        value="<?php echo e($position); ?>">
    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-brand px-4"><i class="bi bi-save me-1"></i> บันทึก</button>
    <a href="index.php" class="btn btn-outline-secondary">ยกเลิก</a>
  </div>
</form>
