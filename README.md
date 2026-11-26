# TATC-IT-Service

ชื่อทีม: TATC Service
กลุ่ม: คภ4/1

Develop A: ธงชัย จุลศรี

Develop B: คุณากร ศรีเกิด

ผู้ใช้:
ครู/นักศึกษา

## MedStock — ระบบจัดการเวชภัณฑ์ห้องพยาบาล

### วิธีติดตั้ง
1. เปิด Apache และ MySQL ใน XAMPP Control Panel
2. นำเข้าฐานข้อมูล: phpMyAdmin → Import → `database/medstock_db.sql`
   (หรือ `C:\xampp\mysql\bin\mysql -u root < database\medstock_db.sql`)
3. เปิด http://localhost/TATC-IT-Service/

ผู้ใช้ตัวอย่างทุกคนใช้รหัสผ่าน `12345678` (เช่น admin@tatc.ac.th)

### โครงสร้าง
| ไฟล์ | หน้าที่ |
|---|---|
| `config.php` | ค่าคงที่ของระบบ, ค่าเชื่อมต่อฐานข้อมูล, `$roles`, ฟังก์ชัน `e()` |
| `db.php` | เชื่อมต่อ mysqli, flash message, CSRF |
| `includes/` | header (sidebar/topbar) และ footer ที่ใช้ร่วมกัน |
| `index.php` | แดชบอร์ด (คำขอเบิกดึงจากฐานข้อมูล) |
| `users/` | จัดการผู้ใช้: รายการ + กรองบทบาท, เพิ่ม, แก้ไข, ลบ |
| `about.php`, `index1.php`, `server.php` | เกี่ยวกับระบบ, ทีมผู้พัฒนา, ข้อมูลเซิร์ฟเวอร์ |

### ระบบเข้าสู่ระบบ
- ทุกหน้าต้องล็อกอินก่อน (`login.php`) ออกจากระบบที่เมนูชื่อผู้ใช้มุมขวาบน
- `require_login()` ใน `db.php` ตรวจการล็อกอิน, `require_login(['admin'])` จำกัดบทบาท
- จัดการผู้ใช้ และ ข้อมูลเซิร์ฟเวอร์ → เฉพาะ admin
- กรอกรหัสผิด 5 ครั้ง ล็อก 60 วินาที · admin ลบหรือลดสิทธิ์ตัวเองไม่ได้

### บทบาทผู้ใช้
| role | ความหมาย | แผนก / ระดับชั้น |
|---|---|---|
| admin | ผู้ดูแลระบบ | ไม่มี (NULL) |
| nurse | พยาบาล / เจ้าหน้าที่ | ไม่มี (NULL) |
| teacher | ครู | มีแผนก ไม่มีระดับชั้น |
| student | นักศึกษา | มีทั้งคู่ |
