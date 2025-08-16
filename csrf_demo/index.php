<?php
session_start();

// สร้าง token เก็บไว้ใน session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>CSRF Demo</title>
</head>
<body>
    <h1>เปลี่ยนอีเมลผู้ใช้</h1>
    <form method="POST" action="process.php">
        <label>อีเมลใหม่: 
            <input type="email" name="email" required>
        </label>
        <!-- ซ่อน CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <button type="submit">บันทึก</button>
    </form>
</body>
</html>
