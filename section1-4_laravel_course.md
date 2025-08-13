# หลักสูตรพัฒนาเว็บแอปพลิเคชันด้วย Laravel + Filament (Section 1-4)

> เนื้อหาสำหรับการสอนนักศึกษา เพื่อปูพื้นฐานก่อนเข้าสู่ Laravel และ Filament

---

## Section 1: Web 101 – ความเข้าใจพื้นฐาน

### เป้าหมาย
- เข้าใจการทำงานของเว็บไซต์แบบ Client-Server
- เข้าใจ HTTP Request/Response
- สร้างหน้าเว็บแบบง่าย ๆ ด้วย HTML, CSS, JavaScript

### Web ทำงานอย่างไร
```
Browser (Client)
   ↓   HTTP Request (GET / POST)
Web Server (Apache/Nginx)
   ↓   รันโค้ด PHP / Laravel ส่ง HTML กลับ
Browser (Client)
   ↑   HTML Page (Response)
```

### HTML พื้นฐาน
```html
<!DOCTYPE html>
<html>
<head>
  <title>My First Page</title>
</head>
<body>
  <h1>Hello, World!</h1>
</body>
</html>
```

### CSS พื้นฐาน
```html
<style>
body { background-color: #f0f0f0; font-family: sans-serif; }
h1 { color: blue; }
</style>
```

### JavaScript พื้นฐาน
```html
<script>
function sayHi() { alert("สวัสดีครับ"); }
</script>
<button onclick="sayHi()">ทักทาย</button>
```

### Mini Workshop
สร้างหน้าเว็บ HTML มีฟอร์มกรอกข้อมูล → กด Submit แล้วแสดงข้อมูลด้วย JS `alert()`

---

## Section 2: PHP Programming เบื้องต้น

### เป้าหมาย
- รู้จัก PHP Syntax
- รับส่งข้อมูลจาก HTML Form
- เชื่อมต่อ MySQL ด้วย PDO

### PHP Syntax
```php
<?php
$name = "Nirusduddin";
$age = 25;
if ($age >= 18) echo "ผู้ใหญ่"; else echo "เด็ก";
?>
```

### รับค่า POST จาก Form
```html
<form action="process.php" method="POST">
  <input type="text" name="name">
  <button type="submit">ส่ง</button>
</form>
```
```php
<?php
$name = $_POST['name'];
echo "สวัสดี $name";
?>
```

### เชื่อมต่อ MySQL ด้วย PDO
```php
$pdo = new PDO("mysql:host=localhost;dbname=testdb;charset=utf8", "root", "");
```

### Mini Workshop
สร้างฟอร์มเก็บ ชื่อ + อีเมล แล้วบันทึกลง MySQL

---

## Section 3: Database Design & Normalization

### เป้าหมาย
- เข้าใจการออกแบบฐานข้อมูล
- รู้จัก Primary Key, Foreign Key, Relationship
- ทำ Normalization ลดข้อมูลซ้ำซ้อน

### ความสัมพันธ์
- 1:1, 1:N, N:M

### ตัวอย่าง Normalize
ก่อน:
```
items(id, name, category_name, category_desc)
```
หลัง:
```
categories(id, name, description)
items(id, name, category_id)
```

### ER Diagram (Borrow System)
- users, categories, items, loans, loan_items, return_slips

### Mini Workshop
ออกแบบ ERD ของ Borrow System พร้อม PK, FK

---

## Section 4: SQL Commands & JOIN

### เป้าหมาย
- ใช้ SQL พื้นฐาน: SELECT, INSERT, UPDATE, DELETE
- JOIN ข้อมูลจากหลายตาราง
- ใช้ GROUP BY และ Aggregate Functions

### SQL พื้นฐาน
```sql
SELECT * FROM items;
INSERT INTO categories (name) VALUES ('Camera');
UPDATE items SET stock = 10 WHERE id = 1;
DELETE FROM items WHERE id = 3;
```

### JOIN
```sql
SELECT items.name, categories.name AS category
FROM items
INNER JOIN categories ON categories.id = items.category_id;
```

### JOIN หลายตาราง
```sql
SELECT loans.id, users.name, items.name, loan_items.qty
FROM loans
INNER JOIN users ON users.id = loans.user_id
INNER JOIN loan_items ON loan_items.loan_id = loans.id
INNER JOIN items ON items.id = loan_items.item_id;
```

### Aggregate Functions
```sql
SELECT categories.name, COUNT(items.id) AS total_items
FROM categories
LEFT JOIN items ON items.category_id = categories.id
GROUP BY categories.name;
```

### Mini Workshop
- แสดงอุปกรณ์พร้อมหมวดหมู่
- แสดงประวัติการยืม (JOIN 3 ตาราง)
- จำนวนอุปกรณ์คงเหลือในแต่ละหมวด
- จำนวนครั้งที่ยืมต่อวัน
