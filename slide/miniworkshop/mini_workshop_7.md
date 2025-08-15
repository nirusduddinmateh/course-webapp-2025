### 1. **สร้างไฟล์ `db.php`** → สำหรับเชื่อมต่อฐานข้อมูล  
```php
<?php
$dsn = "mysql:host=localhost;dbname=test;charset=utf8";
$user = "root";
$pass = "";
$pdo = new PDO($dsn, $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
```

### 2. หน้าเพิ่มสินค้า (`add_product.php`) → ใช้ `INSERT`
```php
<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
    $stmt->execute([$_POST['name'], $_POST['price']]);
    header("Location: list_products.php");
}
?>
<form method="POST">
    ชื่อสินค้า: <input type="text" name="name" required><br>
    ราคา: <input type="number" name="price" step="0.01" required><br>
    <button type="submit">บันทึก</button>
</form>
```

### 3. หน้ารายการสินค้า (`list_products.php`) → ใช้ `SELECT`
```php
<?php
include 'db.php';
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<a href="add_product.php">เพิ่มสินค้า</a>
<table border="1">
    <tr><th>ID</th><th>ชื่อสินค้า</th><th>ราคา</th><th>การจัดการ</th></tr>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['name'] ?></td>
            <td><?= $p['price'] ?></td>
            <td>
                <a href="edit_product.php?id=<?= $p['id'] ?>">แก้ไข</a>
                <a href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('ลบสินค้านี้?')">ลบ</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
```

### 4. หน้าแก้ไขสินค้า (`edit_product.php`) → ใช้ `UPDATE`
```php
<?php
include 'db.php';
$id = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE products SET name=?, price=? WHERE id=?");
    $stmt->execute([$_POST['name'], $_POST['price'], $id]);
    header("Location: list_products.php");
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

### 5. หน้าลบสินค้า (`delete_product.php`) → ใช้ `DELETE`
```php
<?php
include 'db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);
header("Location: list_products.php");
?>
```