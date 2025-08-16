<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (hash_equals($sessionToken, $postedToken)) {
        // ✅ Token ตรงกัน → ยอมให้ทำงาน
        $email = htmlspecialchars($_POST['email']);
        echo "<h2>อัปเดตอีเมลสำเร็จ: {$email}</h2>";
    } else {
        // ❌ ไม่มีหรือ token ไม่ตรง → ป้องกัน CSRF
        http_response_code(403);
        echo "<h2 style='color:red'>CSRF Detected! คำขอถูกปฏิเสธ</h2>";
    }
}
