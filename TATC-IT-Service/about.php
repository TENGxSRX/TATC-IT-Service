<?php
require_once 'config.php';

// อาร์เรย์เก็บเป้าหมายของระบบ MedStock อย่างน้อย 3 ข้อ
$goals = [
    'บริหารจัดการคลังยาและเวชภัณฑ์ห้องพยาบาลอย่างมีประสิทธิภาพ',
    'แจ้งเตือนเมื่อรายการยาใกล้หมดสต็อกหรือใกล้หมดอายุ',
    'ติดตามและควบคุมงบประมาณการเบิกจ่ายยาในระบบ'
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เกี่ยวกับระบบ - <?php echo APP_NAME; ?></title>
</head>
<body>
    <!-- แถบเมนู <nav> มีลิงก์ไปยังทุกหน้า -->
    <nav>
        <a href="index.php">หน้าหลัก (Dashboard)</a> | 
        <a href="about.php">เกี่ยวกับระบบ</a> | 
        <a href="server.php">ข้อมูลเซิร์ฟเวอร์</a>
    </nav>
    <hr>

    <h1>เกี่ยวกับระบบ <?php echo APP_NAME; ?></h1>

    <!-- แสดงจำนวนข้อด้วย count($goals) -->
    <h2>เป้าหมายของระบบ (ทั้งหมด <?php echo count($goals); ?> ข้อ)</h2>

    <!-- แสดงรายการเป้าหมายด้วย <ol> -->
    <ol>
        <?php foreach ($goals as $goal): ?>
            <li><?php echo $goal; ?></li>
        <?php endforeach; ?>
    </ol>
</body>
</html>