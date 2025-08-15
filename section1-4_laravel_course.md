# Section 1 – ภาพรวมเว็บแอปพลิเคชัน (Web Application Overview)

## 🎯 เป้าหมายการเรียนรู้

- เข้าใจว่าเว็บแอปพลิเคชันทำงานอย่างไร (Client–Server)
- รู้จักส่วนประกอบหลัก (Frontend / Backend / Database)
- เข้าใจบทบาท HTML, CSS, JS, PHP
- เห็นภาพว่า Laravel และ Filament เข้ามาอยู่ตรงไหนในภาพรวม

---

## Step 1 – Web Application คืออะไร
**เว็บแอปพลิเคชัน** คือ โปรแกรมที่ทำงานบนเว็บบราวเซอร์ (Chrome, Edge, Firefox, Safari ฯลฯ) โดยมีการสื่อสารกับเซิร์ฟเวอร์ผ่านอินเทอร์เน็ตหรือเครือข่ายภายใน (LAN)

**โครงสร้างพื้นฐาน**
```
[ผู้ใช้/Browser] → ส่ง Request → [Web Server] → ประมวลผล → ส่ง Response กลับ
```
**ตัวอย่างการทำงาน**
1. ผู้ใช้พิมพ์ URL หรือกดปุ่มในเว็บ (Browser)
2. Browser ส่ง HTTP Request ไปยัง Server
3. Server ประมวลผล (เช่น PHP อ่านข้อมูลจาก Database)
4. Server ส่ง HTML/CSS/JS กลับไปให้ Browser แสดงผล

---

## Step 2 – Client–Server Model
💡 **Client (ฝั่งผู้ใช้)**  
- อุปกรณ์ที่ใช้เข้าถึงเว็บ (PC, Mobile)
- ทำงานด้วย HTML + CSS + JS

💡 **Server (ฝั่งประมวลผล)**  
- รัน PHP / Laravel
- เชื่อมต่อฐานข้อมูล (MySQL, PostgreSQL ฯลฯ)
- ตอบสนองข้อมูลในรูปแบบ HTML, JSON


---

## Step 3 – ส่วนประกอบหลักของเว็บ

| ส่วน        | ทำงานบน  | ภาษาหรือเทคโนโลยี       |
|-------------|----------|--------------------------|
| Frontend    | Browser  | HTML, CSS, JavaScript    |
| Backend     | Server   | PHP, Laravel             |
| Database    | Server   | MySQL, MariaDB           |

---

## Step 4 – บทบาทของ HTML, CSS, JS, PHP
- **HTML** → สร้างโครงสร้างเนื้อหา (Structure)
- **CSS** → จัดรูปแบบ (Style)
- **JavaScript** → เพิ่มความโต้ตอบ (Interaction)
- **PHP** → ประมวลผลฝั่ง Server (Backend Logic)
- **MySQL** → เก็บและดึงข้อมูล
- **Laravel** → Framework ช่วยจัดโครงสร้างโค้ด PHP ให้เป็นระบบ
- **Filament** → เครื่องมือสร้างหน้าจอ Admin CRUD ได้รวดเร็ว

---

## Step 5 – วงจรการทำงานของ Laravel
1. **Request** เข้ามาที่ **Route**
2. Route เรียก **Controller**
3. Controller อาจดึงข้อมูลจาก **Model** (ซึ่งติดต่อกับ Database)
4. Controller ส่งข้อมูลไป **View** (Blade Template)
5. View แสดงผลกลับไปที่ Browser

---

## Step 6 – Mini Workshop
**หัวข้อ:** จำลองการส่ง Request/Response

**โจทย์:**  
ให้นักศึกษาเปิด Notepad เขียน HTML ง่าย ๆ แล้วเปิดใน Browser

```html
<!DOCTYPE html>
<html>
<head>
    <title>My First Web</title>
</head>
<body>
    <h1>สวัสดี Laravel!</h1>
    <p>นี่คือเว็บเพจแรกของคุณ</p>
</body>
</html>
```
