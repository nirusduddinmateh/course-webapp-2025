# วิธีติดตั้ง XAMPP PHP 8.2 + DBeaver + Git + Composer (Windows)

---

## 🔹 1. ถอน XAMPP เวอร์ชันเก่าออกก่อน (ถ้ามี)
1. ไปที่ **Control Panel > Programs > Programs and Features**  
2. หา **XAMPP** เวอร์ชันเก่า → **Uninstall**  
3. ลบโฟลเดอร์เก่า เช่น `C:\xampp`  
   - *(หากมี database สำคัญใน `mysql\data` ควร backup ก่อน)*  

---

## 🔹 2. ติดตั้ง XAMPP (PHP 8.2)
1. ดาวน์โหลด XAMPP (PHP 8.2) จาก [Apache Friends](https://www.apachefriends.org/download.html)  
2. เลือกไฟล์ `xampp-windows-x64-8.2.x-0-VS16-installer.exe`  
3. ติดตั้งที่ `C:\xampp`  
4. เปิด **XAMPP Control Panel** → Start `Apache` และ `MySQL`  
5. เข้าเบราว์เซอร์ `http://localhost` → ต้องเจอหน้า Welcome  

---

## 🔹 3. ติดตั้ง DBeaver
1. ดาวน์โหลด [DBeaver](https://dbeaver.io/download/)  
2. เลือก **Windows (Installer)** และติดตั้ง  
3. เปิด DBeaver → เพิ่ม Connection → เลือก **MySQL**  
   - Host: `localhost`  
   - Port: `3306`  
   - User: `root`  
   - Password: (เว้นว่างถ้าไม่ได้ตั้งรหัส)  

---

## 🔹 4. ติดตั้ง Git
1. ดาวน์โหลด [Git for Windows](https://git-scm.com/download/win)  
2. ติดตั้ง (เลือกค่า Default)  
3. ตรวจสอบการติดตั้ง:  
   ```bash
   git --version
   ```

## 🔹 5. ติดตั้ง Composer

1. ดาวน์โหลด Composer-Setup.exe
2. ตอนติดตั้ง เลือก PHP executable:
   ```
   C:\xampp\php\php.exe
   ```
3. ตรวจสอบการติดตั้ง:
   ```
   composer -V
   ```

## 🔹 6. ตรวจสอบสิ่งที่ติดตั้ง

เปิด Command Prompt (CMD) แล้วพิมพ์:
```bash
php -v        # ต้องขึ้น PHP 8.2.x
git --version # ต้องขึ้นเวอร์ชัน git
composer -V   # ต้องขึ้นเวอร์ชัน composer
```