### 1. SQL: สร้างตาราง + seed แอดมิน
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- สร้าง admin เริ่มต้น (รหัสผ่าน = admin123)
```

### 2. สร้าง helpers.php (HTML escape + CSRF)
```php
<?php
// helpers.php
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_time']  = time();
  }
  return $_SESSION['csrf_token'];
}

function csrf_field(): string {
  return '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">';
}

function csrf_verify_or_die(): void {
  $ok = isset($_POST['csrf_token'], $_SESSION['csrf_token'])
     && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
  $expired = isset($_SESSION['csrf_time']) && (time() - $_SESSION['csrf_time'] > 7200);
  if (!$ok || $expired) {
    http_response_code(419);
    exit('CSRF token invalid/expired');
  }
  unset($_SESSION['csrf_token'], $_SESSION['csrf_time']); // หมุน token
}
```

### 3. สร้าง auth.php (login/logout + Guards)
```php
<?php
// auth.php
function login(string $username, string $password): bool {
  global $pdo;
  $st = $pdo->prepare("SELECT id,username,password_hash,role FROM users WHERE username=:u");
  $st->execute([':u'=>$username]);
  $u = $st->fetch();
  if(!$u || !password_verify($password, $u['password_hash'])) return false;

  $_SESSION['user'] = ['id'=>(int)$u['id'],'username'=>$u['username'],'role'=>$u['role']];
  return true;
}

function logout(): void {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'],$p['domain'],$p['secure'],$p['httponly']);
  }
  session_destroy();
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_admin(): bool { return (current_user()['role'] ?? null) === 'admin'; }

function require_login(): void {
  if (!current_user()) { header('Location: login.php'); exit; }
}
function require_admin(): void {
  require_login();
  if (!is_admin()) { http_response_code(403); exit('Forbidden: admin only'); }
}
```

### 4. สร้าง bootstrap.php
```php
<?php
// bootstrap.php
session_start();
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/auth.php';
```

### 5. seed_admin.php (รันครั้งเดียว)
```php
<?php
require_once __DIR__.'/bootstrap.php';

$u = 'admin';
$p = 'admin123';
$hash = password_hash($p, PASSWORD_DEFAULT);

$st = $pdo->prepare("SELECT id FROM users WHERE username=:u");
$st->execute([':u'=>$u]);
if(!$st->fetch()){
  $ins = $pdo->prepare("INSERT INTO users (username,password_hash,role) VALUES (:u,:h,'admin')");
  $ins->execute([':u'=>$u, ':h'=>$hash]);
  echo "Seeded admin / admin123";
} else {
  echo "Admin already exists";
}
```

### 6. สร้าง login.php / logout.php
```php
<?php // login.php
require_once __DIR__.'/bootstrap.php';
if (current_user()) { header('Location: users_list.php'); exit; }

$error = null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify_or_die();
  $u = trim($_POST['username'] ?? '');
  $p = trim($_POST['password'] ?? '');
  if($u && $p && login($u,$p)){ header('Location: users_list.php'); exit; }
  $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!doctype html><meta charset="utf-8"><title>Login</title>
<h1>Login</h1>
<?php if($error): ?><div style="color:red;"><?= e($error) ?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  ผู้ใช้: <input name="username" required>
  รหัสผ่าน: <input name="password" type="password" required>
  <button type="submit">เข้าสู่ระบบ</button>
</form>
```
```php
<?php // logout.php
require_once __DIR__.'/bootstrap.php';
logout();
header('Location: login.php'); exit;
```

### 4. สร้าง เมนู (โชว์ “ผู้ใช้” เฉพาะ admin) `header.partial.php`
```php
<?php $me = current_user(); ?>
<div style="padding:8px;border-bottom:1px solid #ccc">
  <?php if($me): ?>
    <a href="products_list.php">สินค้า</a>
    <?php if(is_admin()): ?> | <a href="users_list.php">ผู้ใช้</a><?php endif; ?>
    | สวัสดี, <?= e($me['username']) ?> (<a href="logout.php">ออกจากระบบ</a>)
  <?php else: ?>
    <a href="login.php">Login</a>
  <?php endif; ?>
</div>
```


### 5. หน้าเพิ่มผู้ใช้ (`user_create.php`) → ใช้ `INSERT`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_admin();
require_once __DIR__.'/header.partial.php';

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify_or_die();
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $role     = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
  if($username==='') $errors[]='กรุณากรอกชื่อผู้ใช้';
  if($password==='') $errors[]='กรุณากรอกรหัสผ่าน';
  if(!$errors){
    try{
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $st = $pdo->prepare("INSERT INTO users (username,password_hash,role) VALUES (:u,:h,:r)");
      $st->execute([':u'=>$username, ':h'=>$hash, ':r'=>$role]);
      header('Location: users_list.php'); exit;
    }catch(Throwable $e){ $errors[]='username ซ้ำหรือข้อมูลไม่ถูกต้อง'; }
  }
}
?>
<h1>เพิ่มผู้ใช้</h1>
<?php foreach($errors as $er) echo '<div style="color:red">'.e($er).'</div>'; ?>
<form method="post">
  <?= csrf_field() ?>
  Username: <input name="username" required><br>
  Password: <input name="password" type="password" required><br>
  Role:
  <select name="role">
    <option value="user">user</option>
    <option value="admin">admin</option>
  </select><br>
  <button type="submit">บันทึก</button>
  <a href="users_list.php">ยกเลิก</a>
</form>
```

### 6. หน้ารายการผู้ใช้ (`users_list.php`) → ใช้ `SELECT`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_admin();
require_once __DIR__.'/header.partial.php';

$rows = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC")->fetchAll();
?>
<h1>ผู้ใช้</h1>
<p><a href="user_create.php">+ เพิ่มผู้ใช้</a></p>
<table border="1" cellpadding="6">
  <tr><th>ID</th><th>Username</th><th>Role</th><th>Created</th><th>จัดการ</th></tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= e($r['id']) ?></td>
      <td><?= e($r['username']) ?></td>
      <td><?= e($r['role']) ?></td>
      <td><?= e($r['created_at']) ?></td>
      <td>
        <a href="user_edit.php?id=<?= e($r['id']) ?>">แก้ไข</a>
        <?php if((int)$r['id'] !== (int)current_user()['id']): ?>
          | <form action="user_delete.php" method="post" style="display:inline" onsubmit="return confirm('ลบผู้ใช้?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= e($r['id']) ?>">
              <button type="submit">ลบ</button>
            </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
```

### 7. หน้าแก้ไขผู้ใช้ (`user_edit.php`) → ใช้ `UPDATE`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_admin();
require_once __DIR__.'/header.partial.php';

$id = (int)($_GET['id'] ?? 0);
$u  = $pdo->prepare("SELECT id, username, role FROM users WHERE id=:id");
$u->execute([':id'=>$id]);
$user = $u->fetch();
if(!$user){ header('Location: users_list.php'); exit; }

$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify_or_die();
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $role     = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
  if($username==='') $errors[]='กรุณากรอกชื่อผู้ใช้';
  if(!$errors){
    try{
      if($password!==''){
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username=:u, password_hash=:h, role=:r WHERE id=:id";
        $pdo->prepare($sql)->execute([':u'=>$username, ':h'=>$hash, ':r'=>$role, ':id'=>$id]);
      } else {
        $sql = "UPDATE users SET username=:u, role=:r WHERE id=:id";
        $pdo->prepare($sql)->execute([':u'=>$username, ':r'=>$role, ':id'=>$id]);
      }
      header('Location: users_list.php'); exit;
    }catch(Throwable $e){ $errors[]='username ซ้ำหรือข้อมูลไม่ถูกต้อง'; }
  }
}
?>
<h1>แก้ไขผู้ใช้ #<?= e($user['id']) ?></h1>
<?php foreach($errors as $er) echo '<div style="color:red">'.e($er).'</div>'; ?>
<form method="post">
  <?= csrf_field() ?>
  Username: <input name="username" required value="<?= e($_POST['username'] ?? $user['username']) ?>"><br>
  Password (ว่าง=ไม่เปลี่ยน): <input name="password" type="password"><br>
  Role:
  <select name="role">
    <option value="user"  <?= $user['role']==='user'?'selected':'' ?>>user</option>
    <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>admin</option>
  </select><br>
  <button type="submit">บันทึก</button>
  <a href="users_list.php">ยกเลิก</a>
</form>
```

### 8. หน้าลบผู้ใช้ (`user_delete.php`) → ใช้ `DELETE`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: users_list.php'); exit; }
csrf_verify_or_die();

$id = (int)($_POST['id'] ?? 0);
if($id > 0 && $id !== (int)current_user()['id']) {
  $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$id]);
}
header('Location: users_list.php'); exit;
```

## อัปเดตหน้า “สินค้า” ให้ปลอดภัย (ล็อกอิน + CSRF บน POST)

หลักการ:
- ทุกหน้าเพิ่ม/แก้/ลบ: require_login()
- ฟอร์ม POST ใส่ <?= csrf_field() ?> และตรวจ csrf_verify_or_die()
- เปลี่ยนลบจากลิงก์ GET → ฟอร์ม POST

### 1. หน้าเพิ่มสินค้า (`product_create.php`) → ใช้ `INSERT`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify_or_die();
  $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
  $stmt->execute([$_POST['name'], $_POST['price']]);
  header("Location: products_list.php"); exit;
}
require_once __DIR__.'/header.partial.php';
?>
<form method="POST">
  <?= csrf_field() ?>
  ชื่อสินค้า: <input type="text" name="name" required><br>
  ราคา: <input type="number" name="price" step="0.01" required><br>
  <button type="submit">บันทึก</button>
</form>
```

### 2. หน้ารายการสินค้า (`products_list.php`) → ใช้ `SELECT`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();
require_once __DIR__.'/header.partial.php';

$stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<a href="product_create.php">+ เพิ่มสินค้า</a>
<table border="1">
  <tr><th>ID</th><th>ชื่อสินค้า</th><th>ราคา</th><th>การจัดการ</th></tr>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?= e($p['id']) ?></td>
      <td><?= e($p['name']) ?></td>
      <td><?= e($p['price']) ?></td>
      <td>
        <a href="product_edit.php?id=<?= e($p['id']) ?>">แก้ไข</a>
        |
        <form action="product_delete.php" method="post" style="display:inline" onsubmit="return confirm('ลบสินค้านี้?')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e($p['id']) ?>">
          <button type="submit">ลบ</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
```

### 3. หน้าแก้ไขสินค้า (`product_edit.php`) → ใช้ `UPDATE`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify_or_die();
  $stmt = $pdo->prepare("UPDATE products SET name=?, price=? WHERE id=?");
  $stmt->execute([$_POST['name'], $_POST['price'], $id]);
  header("Location: products_list.php"); exit;
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();
require_once __DIR__.'/header.partial.php';
?>
<form method="POST">
  <?= csrf_field() ?>
  ชื่อสินค้า: <input type="text" name="name" value="<?= e($product['name']) ?>" required><br>
  ราคา: <input type="number" name="price" step="0.01" value="<?= e($product['price']) ?>" required><br>
  <button type="submit">บันทึกการแก้ไข</button>
</form>
```

### 4. หน้าลบสินค้า (`product_delete.php`) → ใช้ `DELETE`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();
if($_SERVER['REQUEST_METHOD'] !== 'POST'){ header('Location: products_list.php'); exit; }
csrf_verify_or_die();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);
header("Location: products_list.php"); exit;
```

### Checklist เพื่อทดสอบ
- รัน SQL ตาราง users → รัน seed_admin.php (admin/admin123)
- เข้าที่ login.php → ล็อกอิน
- เมนูบน: เห็น สินค้า ทุกคน (ที่ล็อกอิน), เห็น ผู้ใช้ เฉพาะ admin
- ฟอร์มทุกหน้า POST ต้องมี CSRF และตรวจสอบได้
- ลองล็อกอินเป็น user ธรรมดา → เข้าหน้า users_list.php ควรถูก 403

## เสริมการใช้ Tailwinds CSS 
### 1. เพิ่มไฟล์ Layout ส่วนกลาง `layout_top.php` และ `layout_bottom.php`
```php
<?php // layout_top.php
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Mini Workshop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind (CDN) + forms plugin -->
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php /* เปิด wrapper */ ?>
<div class="min-h-screen">
```
```php
<?php // layout_bottom.php
?>
</div> <!-- /min-h-screen -->
</body>
</html>
```

### 2. อัปเดตให้เป็น navbar `header.partial.php`
```php
<?php
// header.partial.php
$me = current_user();
?>
<header class="bg-white/70 backdrop-blur border-b">
  <nav class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between">
    <a href="list_products.php" class="font-bold text-lg tracking-wide">Mini Workshop</a>

    <div class="flex items-center gap-4">
      <?php if ($me): ?>
        <a href="products_list.php" class="text-sm hover:text-indigo-600">สินค้า</a>
        <?php if (is_admin()): ?>
          <a href="users_list.php" class="text-sm hover:text-indigo-600">ผู้ใช้</a>
        <?php endif; ?>
        <span class="hidden sm:inline text-sm text-gray-500">| สวัสดี, <?= e($me['username']) ?></span>
        <a href="logout.php"
           class="inline-flex items-center rounded-lg border px-3 py-1.5 text-sm hover:bg-gray-50">
           ออกจากระบบ
        </a>
      <?php else: ?>
        <a href="login.php"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
           เข้าสู่ระบบ
        </a>
      <?php endif; ?>
    </div>
  </nav>
</header>
```

### 3. อัปเดต `login.php`
```php
<?php
require_once __DIR__.'/bootstrap.php';
if(current_user()){ header('Location: list_products.php'); exit; }

$error = null;
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_verify_or_die();
  $u = trim($_POST['username'] ?? '');
  $p = trim($_POST['password'] ?? '');
  if($u && $p && login($u,$p)){ header('Location: list_products.php'); exit; }
  $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}

require_once __DIR__.'/layout_top.php';
?>
<div class="grid place-items-center min-h-[80vh] px-4">
  <div class="w-full max-w-sm bg-white rounded-2xl shadow p-6">
    <h1 class="text-xl font-semibold mb-4">เข้าสู่ระบบ</h1>

    <?php if($error): ?>
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-medium">ผู้ใช้</label>
        <input name="username" required class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500">
      </div>
      <div>
        <label class="text-sm font-medium">รหัสผ่าน</label>
        <input name="password" type="password" required class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500">
      </div>
      <button type="submit"
        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-white font-medium hover:bg-indigo-700">
        เข้าสู่ระบบ
      </button>
    </form>
  </div>
</div>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
```

### 4. อัปเดต `products_list.php`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();

$stmt = $pdo->query("SELECT id, name, price FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

require_once __DIR__.'/layout_top.php';
require_once __DIR__.'/header.partial.php';
?>
<main class="mx-auto max-w-6xl p-4 sm:p-6">
  <div class="flex items-center justify-between gap-4">
    <h1 class="text-xl sm:text-2xl font-semibold">รายการสินค้า</h1>
    <a href="product_create.php"
       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
       + เพิ่มสินค้า
    </a>
  </div>

  <div class="mt-4 overflow-x-auto bg-white rounded-2xl shadow">
    <table class="w-full text-left text-sm">
      <thead class="bg-gray-100 text-gray-700 text-xs uppercase">
        <tr>
          <th class="px-4 py-3">ID</th>
          <th class="px-4 py-3">ชื่อสินค้า</th>
          <th class="px-4 py-3">ราคา</th>
          <th class="px-4 py-3">การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr class="border-t hover:bg-gray-50">
            <td class="px-4 py-3"><?= e($p['id']) ?></td>
            <td class="px-4 py-3"><?= e($p['name']) ?></td>
            <td class="px-4 py-3"><?= e($p['price']) ?></td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a href="edit_product.php?id=<?= e($p['id']) ?>"
                   class="inline-flex items-center rounded-md border px-3 py-1.5 text-xs font-medium hover:bg-gray-50">
                   แก้ไข
                </a>
                <form action="delete_product.php" method="post"
                      onsubmit="return confirm('ลบสินค้านี้?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                  <button type="submit"
                    class="inline-flex items-center rounded-md border border-rose-300 text-rose-700 px-3 py-1.5 text-xs font-medium hover:bg-rose-50">
                    ลบ
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$products): ?>
          <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">ยังไม่มีสินค้า</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
```


### 5. อัปเดต `product_create.php`
```php
<?php
require_once __DIR__.'/bootstrap.php';
require_login();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify_or_die();
  $name  = trim($_POST['name'] ?? '');
  $price = trim($_POST['price'] ?? '');
  if ($name === '') $errors[] = 'กรุณากรอกชื่อสินค้า';
  if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = 'ราคาไม่ถูกต้อง';

  if (!$errors) {
    $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
    $stmt->execute([$name, $price]);
    header("Location: list_products.php"); exit;
  }
}

require_once __DIR__.'/layout_top.php';
require_once __DIR__.'/header.partial.php';
?>
<main class="mx-auto max-w-3xl p-4 sm:p-6">
  <h1 class="text-xl sm:text-2xl font-semibold">เพิ่มสินค้า</h1>

  <?php if($errors): ?>
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
      <?php foreach($errors as $er) echo '<div>'.e($er).'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="mt-4 space-y-4 bg-white rounded-2xl shadow p-6">
    <?= csrf_field() ?>
    <div>
      <label class="text-sm font-medium">ชื่อสินค้า</label>
      <input type="text" name="name" required
             class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500"
             value="<?= e($_POST['name'] ?? '') ?>">
    </div>
    <div>
      <label class="text-sm font-medium">ราคา</label>
      <input type="number" name="price" step="0.01" min="0" required
             class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500"
             value="<?= e($_POST['price'] ?? '0') ?>">
    </div>
    <div class="flex items-center gap-2">
      <button type="submit"
        class="rounded-lg bg-indigo-600 px-4 py-2 text-white font-medium hover:bg-indigo-700">
        บันทึก
      </button>
      <a href="list_products.php" class="rounded-lg border px-4 py-2 hover:bg-gray-50">ยกเลิก</a>
    </div>
  </form>
</main>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
```


### 6. อัปเดต `product_edit.php`
```php
<?php
// edit_product.php
require_once __DIR__.'/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$errors = [];

/** โหลดข้อมูลเดิม */
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = :id");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
  // ไม่พบสินค้า
  require_once __DIR__.'/layout_top.php';
  require_once __DIR__.'/header.partial.php';
  ?>
  <main class="mx-auto max-w-3xl p-4 sm:p-6">
    <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-6">
      <h1 class="text-xl font-semibold text-yellow-800">ไม่พบสินค้า</h1>
      <p class="mt-2 text-yellow-700">รหัสที่ระบุ: <?= e($id) ?></p>
      <a href="list_products.php" class="mt-4 inline-flex rounded-lg border px-4 py-2 hover:bg-gray-50">กลับหน้ารายการ</a>
    </div>
  </main>
  <?php
  require_once __DIR__.'/layout_bottom.php';
  exit;
}

/** เมื่อบันทึก */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify_or_die();

  $name  = trim($_POST['name'] ?? '');
  $price = trim($_POST['price'] ?? '');

  if ($name === '') $errors[] = 'กรุณากรอกชื่อสินค้า';
  if ($price === '' || !is_numeric($price) || $price < 0) $errors[] = 'ราคาไม่ถูกต้อง';

  if (!$errors) {
    $u = $pdo->prepare("UPDATE products SET name = :n, price = :p WHERE id = :id");
    $u->execute([':n' => $name, ':p' => $price, ':id' => $product['id']]);
    header("Location: list_products.php?updated=1");
    exit;
  }

  // ถ้าผิดพลาด เก็บค่าที่ผู้ใช้กรอกทับตัวแปร $product เพื่อโชว์ในฟอร์ม
  $product['name']  = $name;
  $product['price'] = $price;
}

require_once __DIR__.'/layout_top.php';
require_once __DIR__.'/header.partial.php';
?>
<main class="mx-auto max-w-3xl p-4 sm:p-6">
  <h1 class="text-xl sm:text-2xl font-semibold">แก้ไขสินค้า #<?= e($product['id']) ?></h1>

  <?php if ($errors): ?>
    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
      <?php foreach ($errors as $er) echo '<div>'.e($er).'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="POST" class="mt-4 space-y-4 bg-white rounded-2xl shadow p-6">
    <?= csrf_field() ?>
    <div>
      <label class="text-sm font-medium">ชื่อสินค้า</label>
      <input
        type="text"
        name="name"
        required
        class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500"
        value="<?= e($product['name']) ?>">
    </div>

    <div>
      <label class="text-sm font-medium">ราคา</label>
      <input
        type="number"
        name="price"
        step="0.01"
        min="0"
        required
        class="mt-1 block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500"
        value="<?= e($product['price']) ?>">
    </div>

    <div class="flex items-center gap-2">
      <button type="submit"
        class="rounded-lg bg-indigo-600 px-4 py-2 text-white font-medium hover:bg-indigo-700">
        บันทึกการแก้ไข
      </button>
      <a href="list_products.php" class="rounded-lg border px-4 py-2 hover:bg-gray-50">
        ยกเลิก
      </a>
    </div>
  </form>
</main>
<?php require_once __DIR__.'/layout_bottom.php'; ?>
```