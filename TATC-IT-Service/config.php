<?php
// ค่าตั้งของระบบ MedStock ใช้ร่วมกันทุกหน้า
const APP_NAME = 'MedStock — ระบบจัดการเวชภัณฑ์';
const TEAM_NAME = 'TATC Service';
const TEAM_GROUP = 'คภ4/1';
const ALERT_LOW_STOCK = 50;      // เกณฑ์เตือนยาใกล้หมดสต็อก (อ้างอิงจากตารางใบงาน)
const ALERT_EXPIRY_DAYS = 30;    // แจ้งเตือนยาล่วงหน้าก่อนหมดอายุ (วัน)
const ALERT_PERCENT = 80;        // เกณฑ์เปอร์เซ็นต์แจ้งเตือนงบประมาณ (%)

// การเชื่อมต่อฐานข้อมูล (ค่าเริ่มต้นของ XAMPP)
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'medstock_db';

// ตั้งค่าเขตเวลาให้เป็นประเทศไทย (เพื่อให้แสดงวันที่ตรงกับเวลาปัจจุบัน)
date_default_timezone_set('Asia/Bangkok');

// บทบาทผู้ใช้ (ต้องตรงกับ ENUM ในตาราง users) ใช้ซ้ำในหน้ารายการ/เพิ่ม/แก้ไข
$roles = [
    'admin'   => 'ผู้ดูแลระบบ',
    'nurse'   => 'พยาบาล / เจ้าหน้าที่',
    'teacher' => 'ครู',
    'student' => 'นักศึกษา',
];

// ป้องกันอักขระพิเศษก่อนแสดงผล (กัน XSS)
function e($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
