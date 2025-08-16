### 1. **สร้างไฟล์ `db.php`** → สำหรับเชื่อมต่อฐานข้อมูล  
```php
<?php
// ปรับค่าตามเครื่องคุณ
$host    = '127.0.0.1';
$db      = 'miniworkshop7'; // ชื่อฐานข้อมูลที่คุณสร้างไว้
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

// สร้าง DSN (ระบุ charset utf8mb4 ให้รองรับภาษาไทย/อีโมจิ)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// ตัวเลือกเพื่อความปลอดภัย/สะดวกเวลาใช้งาน
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // โยน Exception เมื่อเกิดข้อผิดพลาด
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // คืนข้อมูลแบบแอสโซซิเอทีฟ
    PDO::ATTR_EMULATE_PREPARES   => false,                  // ใช้ Native Prepared Statement
    // PDO::ATTR_PERSISTENT      => true,                   // (ทางเลือก) ใช้ persistent connection
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // (ทางเลือก) ตั้ง timezone ให้ตรงระบบ
    // $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    // โปรดหลีกเลี่ยงการแสดงรายละเอียด error ต่อผู้ใช้จริงใน production
    http_response_code(500);
    exit('เชื่อมต่อฐานข้อมูลล้มเหลว');
}
```

### 2. หน้าเพิ่มสินค้า (`product_create.php`) → ใช้ `INSERT`
```php
<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['price']]);
    header("Location: products_list.php");
}
?>
<form method="POST">
    ชื่อสินค้า: <input type="text" name="name" required><br>
    ราคา: <input type="number" name="price" step="0.01" required><br>
    <button type="submit">บันทึก</button>
</form>
```

### 3. หน้ารายการสินค้า (`products_list.php`) → ใช้ `SELECT`
```php
<?php
include 'db.php';
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<a href="product_create.php">เพิ่มสินค้า</a>
<table border="1">
    <tr><th>ID</th><th>ชื่อสินค้า</th><th>ราคา</th><th>การจัดการ</th></tr>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['name'] ?></td>
            <td><?= $p['price'] ?></td>
            <td>
                <a href="product_edit.php?id=<?= $p['id'] ?>">แก้ไข</a>
                <a href="product_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('ลบสินค้านี้?')">ลบ</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
```

### 4. หน้าแก้ไขสินค้า (`product_edit.php`) → ใช้ `UPDATE`
```php
<?php
include 'db.php';
$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE products SET name=?, price=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['price'], $id]);
    header("Location: products_list.php");
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<form method="POST">
    ชื่อสินค้า: <input type="text" name="name" value="<?= $product['name'] ?>" required><br>
    ราคา: <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required><br>
    <button type="submit">บันทึกการแก้ไข</button>
</form>
```

### 5. หน้าลบสินค้า (`product_delete.php`) → ใช้ `DELETE`
```php
<?php
include 'db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);
header("Location: products_list.php");
?>
```