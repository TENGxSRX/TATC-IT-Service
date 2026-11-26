-- =====================================================
-- ฐานข้อมูล MedStock — ระบบจัดการเวชภัณฑ์ห้องพยาบาล
-- นำเข้าด้วย phpMyAdmin (แท็บ Import) หรือคำสั่ง:
--   C:\xampp\mysql\bin\mysql -u root < database\medstock_db.sql
-- รหัสผ่านของผู้ใช้ตัวอย่างทุกคน: 12345678
-- =====================================================

CREATE DATABASE IF NOT EXISTS medstock_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medstock_db;

-- ลบตารางลูกก่อนตารางแม่ (ไม่งั้นติด Foreign Key)
DROP TABLE IF EXISTS requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS levels;
DROP TABLE IF EXISTS departments;

-- แผนกวิชา
CREATE TABLE departments (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ระดับชั้น (ใช้กับนักศึกษาเท่านั้น)
CREATE TABLE levels (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ผู้ใช้ระบบ
CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,            -- เก็บเฉพาะ hash ห้ามเก็บรหัสจริง
  full_name     VARCHAR(150) NOT NULL,
  role          ENUM('admin','nurse','teacher','student') NOT NULL,
  department_id INT NULL,                         -- NULL = ไม่มีแผนก
  level_id      INT NULL,                         -- NULL = ไม่มีระดับชั้น
  position      VARCHAR(100) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_users_level      FOREIGN KEY (level_id)      REFERENCES levels(id)
) ENGINE=InnoDB;

-- คำขอเบิกเวชภัณฑ์ (ผู้ใช้ที่มีคำขอจะลบไม่ได้ → error 1451)
CREATE TABLE requests (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(20)  NOT NULL UNIQUE,
  requester_id INT NOT NULL,
  item         VARCHAR(200) NOT NULL,
  status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_requests_user FOREIGN KEY (requester_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO departments (id, name) VALUES
  (1, 'เทคโนโลยีสารสนเทศ'),
  (2, 'ช่างยนต์'),
  (3, 'การบัญชี'),
  (4, 'ช่างไฟฟ้ากำลัง'),
  (5, 'สามัญสัมพันธ์');

INSERT INTO levels (id, name) VALUES
  (1, 'ปวช.1'), (2, 'ปวช.2'), (3, 'ปวช.3'), (4, 'ปวส.1'), (5, 'ปวส.2');

-- hash ของ '12345678' (สร้างด้วย password_hash)
SET @pw = '$2y$10$SMNLoDUs4FahL6KOuL4Y5uXZfCo8ZZGyaEzvkmKDJ9r45EN1HrHqS';

INSERT INTO users (id, email, password_hash, full_name, role, department_id, level_id, position) VALUES
  (1,  'admin@tatc.ac.th',    @pw, 'ผู้ดูแลระบบ',        'admin',   NULL, NULL, 'ฝ่ายบริหาร'),
  (2,  'nurse@tatc.ac.th',    @pw, 'เจน ใจดี',           'nurse',   NULL, NULL, 'พยาบาลวิชาชีพ'),
  (3,  'somchai@tatc.ac.th',  @pw, 'สมชาย รักกีฬา',      'teacher', 5,    NULL, 'ครูพลศึกษา'),
  (4,  'malee@tatc.ac.th',    @pw, 'มาลี ศรีสุข',        'teacher', 1,    NULL, 'ครูประจำแผนก'),
  (5,  'nareekan@tatc.ac.th', @pw, 'นรีกานต์ มีสุข',     'student', 1,    4,    NULL),
  (6,  'wipawee@tatc.ac.th',  @pw, 'วิภาวี แสงทอง',      'student', 3,    2,    NULL),
  (7,  'thanakorn@tatc.ac.th',@pw, 'ธนกร ใจกล้า',        'student', 2,    3,    NULL),
  (8,  'panita@tatc.ac.th',   @pw, 'ปณิตา รุ่งเรือง',    'student', 3,    5,    NULL),
  (9,  'kittipong@tatc.ac.th',@pw, 'กิตติพงษ์ ขยันเรียน','student', 4,    1,    NULL),
  (10, 'pimchanok@tatc.ac.th',@pw, 'พิมพ์ชนก ดีงาม',     'teacher', 3,    NULL, 'หัวหน้าแผนก');

INSERT INTO requests (code, requester_id, item, status, created_at) VALUES
  ('REQ-008', 8, 'ผ้าก๊อซแบบแผ่น (2 ซอง)',        'approved', NOW() - INTERVAL 2 DAY),
  ('REQ-009', 7, 'น้ำเกลือล้างแผล (1 ขวด)',        'rejected', NOW() - INTERVAL 1 DAY - INTERVAL 2 HOUR),
  ('REQ-010', 6, 'พลาสเตอร์ยา (1 กล่อง)',          'approved', NOW() - INTERVAL 1 DAY),
  ('REQ-011', 3, 'แอลกอฮอล์ 70% 450ml (2 ขวด)',   'pending',  NOW() - INTERVAL 3 HOUR),
  ('REQ-012', 5, 'พาราเซตามอล 500mg (10 แผง)',     'pending',  NOW() - INTERVAL 1 HOUR);
