<?php
// สาธิตง่ายๆ ไม่ใช้ session / ไม่ตรวจ token
?>
<!doctype html>
<html lang="th">
<head><meta charset="UTF-8"><title>NO CSRF Demo</title></head>
<body>
  <h1>เปลี่ยนอีเมล (ไม่มี CSRF)</h1>
  <form method="POST" action="insecure_process.php">
    <label>อีเมลใหม่:
      <input type="email" name="email" required>
    </label>
    <!-- ไม่มี hidden csrf_token -->
    <button type="submit">บันทึก</button>
  </form>
</body>
</html>
