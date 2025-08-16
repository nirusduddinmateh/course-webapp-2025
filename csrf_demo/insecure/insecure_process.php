<?php
// รับ POST แล้วถือว่าเชื่อถือได้ทันที (ไม่ควรทำในงานจริง)
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
if ($email !== '') {
    echo "<h2>อัปเดตอีเมลสำเร็จ (โดยไม่ตรวจ CSRF): {$email}</h2>";
} else {
    echo "<h2>ไม่พบค่า email</h2>";
}
