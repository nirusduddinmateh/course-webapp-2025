# Section 8 – ติดตั้งและโครงสร้างโปรเจกต์ Laravel 11

🎯 **เป้าหมายการเรียนรู้**  
1. ติดตั้ง **Laravel 11** บนเครื่องตัวเองได้
2. เข้าใจโครงสร้างโฟลเดอร์ของ Laravel
3. รันเซิร์ฟเวอร์ Laravel และทดสอบได้
4. รู้จักไฟล์ **.env** และการตั้งค่า Database เบื้องต้น

## Step 1 – เตรียมเครื่อง

ติดตั้งซอฟต์แวร์จำเป็นก่อนเริ่มใช้งาน Laravel:

1. **PHP เวอร์ชัน 8.2 ขึ้นไป**  
   ตรวจสอบเวอร์ชัน:
   ```bash
   php -v
   ```
2. **Composer (PHP package manager)**  
   ตรวจสอบเวอร์ชัน:
   ```bash
   composer -V
   ```
3. **MySQL/MariaDB (ใช้ DB จากสัปดาห์ที่ 1 ได้เลย)**  
4. **VS Code (พร้อม Extensions: Laravel Blade Snippets, Laravel Artisan)**  

## Step 2 – สร้างโปรเจกต์ Laravel 11

1. เปิด **Terminal** ไปยังโฟลเดอร์ที่ต้องการเก็บโปรเจกต์

2. รันคำสั่งติดตั้ง Laravel 11:
   ```bash
   composer create-project laravel/laravel myapp "11.*"
   ```
3. เข้าสู่โฟลเดอร์โปรเจกต์:
   ```bash
   cd myapp
   ```
4. รันเซิร์ฟเวอร์ทดสอบ:
   ```bash
   php artisan serve
   ```
5. เปิด Browser ไปที่
   👉 http://127.0.0.1:8000
   จะเห็นหน้า Laravel Welcome

## Step 3 – โครงสร้างโปรเจกต์

| โฟลเดอร์/ไฟล์            | หน้าที่ |
|---------------------------|---------------------------------------------------|
| **app/**                  | เก็บโค้ดหลัก (Models, Controllers, Middleware)   |
| **routes/web.php**        | กำหนดเส้นทางเว็บ (**Routing**)                   |
| **resources/views/**      | เก็บไฟล์ **Blade Template** (HTML + Blade Syntax) |
| **database/migrations/**  | ไฟล์กำหนดโครงสร้างตารางฐานข้อมูล                |
| **.env**                  | เก็บการตั้งค่าระบบ เช่น DB, APP_KEY              |
| **public/**               | โฟลเดอร์ root ของเว็บ (เก็บ asset, index.php)    |

## Step 4 – การตั้งค่า .env

เปิดไฟล์ **.env** และตั้งค่าฐานข้อมูลให้ตรงกับเครื่องของนักศึกษา

**ตัวอย่างการตั้งค่า:**
```env
APP_NAME="MyApp"
APP_ENV=local
APP_KEY=base64:xxxxxx
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopdb
DB_USERNAME=root
DB_PASSWORD=
```
⚠️ ข้อควรระวัง:
ห้ามแชร์ไฟล์ .env ขึ้น GitHub หรือที่สาธารณะ
เพราะภายในมีข้อมูลสำคัญ เช่น Database Password และ Application Key

## Step 5 – Artisan Command เบื้องต้น

**Artisan** คือ CLI ของ Laravel ที่ช่วยทำงานซ้ำ ๆ ให้เร็วขึ้น

### พื้นฐานที่ใช้บ่อย
```bash
php artisan serve            # รันเซิร์ฟเวอร์ (http://127.0.0.1:8000)
php artisan route:list       # แสดง Route ทั้งหมด
php artisan tinker           # โหมด interactive เข้าถึงโค้ด/โมเดล
php artisan about            # ข้อมูลสภาพแวดล้อมโปรเจกต์ (เวอร์ชัน/ไดรเวอร์ ฯลฯ)
```

### สร้างโค้ดอัตโนมัติ (Scaffolding)
```bash
php artisan make:model Product -mcr
# -m สร้าง migration
# -c สร้าง controller
# -r ทำเป็น resource controller (index/create/store/show/edit/update/destroy)

php artisan make:controller ProductController -r
php artisan make:migration create_products_table
php artisan make:seeder ProductSeeder
php artisan make:factory ProductFactory --model=Product
php artisan make:request StoreProductRequest
```

### จัดการฐานข้อมูล (Migration/Seed)
```bash
php artisan migrate                  # รัน migration ที่ยังไม่เคยรัน
php artisan migrate:rollback         # ย้อนกลับ batch ล่าสุด
php artisan migrate:fresh --seed     # ล้างตาราง + migrate ใหม่ + seed
php artisan db:seed --class=ProductSeeder
```

### เคลียร์และคอมไพล์แคช (เวลาแก้ .env/route/view แล้วไม่อัปเดต)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
# (โปรดักชัน) คอมไพล์ให้เร็วขึ้น:
php artisan config:cache
php artisan route:cache
```

### ยูทิลิตี้ที่มีประโยชน์
```bash
php artisan storage:link   # ลิงก์ storage/app/public -> public/storage
php artisan queue:work     # รันคิวงาน (ถ้าใช้คิว)
php artisan test           # รัน PHPUnit/Pest
php artisan key:generate   # สร้าง APP_KEY (ครั้งแรกหลังติดตั้ง)
```
#### เพิ่มเติม
- ดูคำสั่งทั้งหมด: `php artisan list`
- ดูวิธีใช้คำสั่ง: `php artisan <command> -h`

## Step 6 – Mini Workshop

**โจทย์:**  
ให้แต่ละคนติดตั้ง **Laravel 11** บนเครื่อง และสร้างหน้าเว็บแรกของตัวเอง

---

1. **แก้ไฟล์ `routes/web.php`**
```php
Route::get('/hello', function () {
    return "สวัสดี Laravel!";
});
```
2. **ทดสอบเปิด `http://127.0.0.1:8000/hello`**
3. **แก้หน้า Welcome (`resources/views/welcome.blade.php`) ให้ใส่ชื่อ-รหัสนักศึกษา**


# Section 9 – Routing & Controller

🎯 **เป้าหมายการเรียนรู้**  

- เข้าใจการทำงานของ **Route** ใน Laravel
- สร้าง Route แบบง่าย, การส่งค่า, และการจัดกลุ่ม Route ได้
- สร้าง **Controller** เพื่อแยกโค้ดออกจาก Route
- ส่งข้อมูลจาก **Controller** ไปยัง **View** ได้

## Step 1 – Route คืออะไร

**Route** คือการกำหนดว่า **URL** ใด จะให้ Laravel ทำงานอะไร  

Laravel มี Route หลักอยู่ในไฟล์:

- `routes/web.php` → สำหรับหน้าเว็บ (**HTML**)  
- `routes/api.php` → สำหรับ API (**JSON**)  

## Step 2 – Route พื้นฐาน

เปิดไฟล์ **`routes/web.php`** แล้วเพิ่ม:

```php
use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return "สวัสดี Laravel!";
});
```
- `Route::get('/hello', ... )` → รับ Request แบบ GET
- สามารถใช้ `post`, `put`, `delete` แทนได้ตามชนิดของ Request

## Step 3 – Route พร้อมส่งค่า (Parameter)

```php
Route::get('/hello/{name}', function ($name) {
    return "สวัสดีคุณ $name";
});
```

- URL: `/hello/Somchai` → จะแสดง “สวัสดีคุณ Somchai”
- สามารถกำหนดเงื่อนไขได้:
```php
Route::get('/user/{id}', function ($id) {
    return "User ID: $id";
})->whereNumber('id');
```
- `whereNumber('id')` → กำหนดให้ `{id}` ต้องเป็นตัวเลขเท่านั้น

## Step 4 – Route พร้อมชื่อ (Named Route)

```php
Route::get('/profile', function () {
    return "หน้าโปรไฟล์";
})->name('profile');
```
การใช้งานใน View (Blade):
```blade
<a href="{{ route('profile') }}">ไปโปรไฟล์</a>
```
- `->name('profile')` → ตั้งชื่อ Route เป็น profile
- สามารถเรียกใช้งาน Route ได้โดยใช้ `route('profile')`

## Step 5 – Route Group

```php
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return "Admin Dashboard";
    });
    Route::get('/users', function () {
        return "Manage Users";
    });
});
```

การเรียกใช้งาน:
- `/admin/dashboard` → แสดง "Admin Dashboard"
- `/admin/users` → แสดง "Manage Users"

## Step 6 – สร้าง Controller

1. **สร้าง Controller ด้วย Artisan**
```bash
php artisan make:controller PageController
```
- Laravel จะสร้างไฟล์ `app/Http/Controllers/PageController.php`

2. เพิ่มเมธอดใน Controller
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return "นี่คือหน้าเกี่ยวกับเรา (About)";
    }

    public function contact()
    {
        return "นี่คือหน้าติดต่อเรา (Contact)";
    }
}
```

2. การเรียก Controller ใน Route
```php
use App\Http\Controllers\PageController;

Route::get('/about', [PageController::class, 'about']);
Route::get('/contact', [PageController::class, 'contact']);
```

## Step 7 – ส่งข้อมูลจาก Controller ไป View

เป้าหมาย: สร้างหน้า **/profile** และส่งค่าจาก Controller ไปแสดงใน Blade

---

### 1.สร้าง View
**ไฟล์:** `resources/views/profile.blade.php`
```blade
<h1>โปรไฟล์ของ {{ $name }}</h1>
<p>อายุ: {{ $age }} ปี</p>
```
- หมายเหตุ: `{{ ... }}` จะ escape HTML ให้ปลอดภัยอัตโนมัติ

### 2. Controller
**ไฟล์:** `app/Http/Controllers/PageController.php`
```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function profile(): View
    {
        $name = "สมชาย ใจดี";
        $age  = 21;

        // วิธีที่ 1: compact()
        return view('profile', compact('name', 'age'));

        // วิธีที่ 2: array
        // return view('profile', ['name' => $name, 'age' => $age]);

        // วิธีที่ 3: chain with()
        // return view('profile')->with('name', $name)->with('age', $age);
    }
}
```
- สร้างไฟล์ Controller ด่วน: `php artisan make:controller PageController`

### 3. Route
**ไฟล์:** `routes/web.php`
```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('/profile', [PageController::class, 'profile']);
```

## Step 8 – Mini Workshop

**โจทย์:**  
สร้าง Controller ชื่อ `ProductController` พร้อม Route ดังนี้:

1. `/products` → แสดงข้อความ `"รายการสินค้า"`
2. `/products/{id}` → แสดงข้อความ `"รายละเอียดสินค้ารหัส: X"`
3. `/products/create` → แสดงข้อความ `"เพิ่มสินค้าใหม่"`
4. ใช้ **Route Group** โดย Prefix `/products`

**ขั้นตอน**
- สร้าง Controller `php artisan make:controller ProductController`
- เขียนเมธอด `index`, `show($id)`, `create`
- กำหนด Route Group ให้ครบ

# Section 10 – Model & Migration

🎯 **เป้าหมายการเรียนรู้**  

1. เข้าใจความหมายของ **Model** และ **Migration**
2. สร้างและรัน **Migration** ได้
3. ใช้ **Model** เพื่อเชื่อมกับตารางในฐานข้อมูล
4. เพิ่ม/แก้ไข/ลบข้อมูลผ่าน **Laravel Eloquent ORM** ได้

## Step 1 – Model คืออะไร

**Model** = ตัวแทนของ “ตาราง” ในฐานข้อมูล ใช้ติดต่อ/จัดการข้อมูลผ่าน **Eloquent ORM**  
ไฟล์ Model อยู่ที่ `app/Models/` (เช่น `app/Models/Product.php`)

### สร้าง Model
```bash
php artisan make:model Product
# หรือพร้อม Migration/Controller/Resource
php artisan make:model Product -mcr
```

**ใช้งานเบื้องต้น (Eloquent)**
```php
use App\Models\Product;

// อ่าน
$all  = Product::all();       // SELECT * FROM products
$one  = Product::find(1);     // SELECT ... WHERE id=1
$some = Product::where('price', '>', 100)->get();

// เพิ่ม
Product::create(['name' => 'Mouse', 'price' => 250]);

// แก้ไข
$p = Product::find(1);
$p->price = 299;
$p->save();

// ลบ
Product::destroy(1);
```
> หมายเหตุ: ใช้ `create()` ต้องตั้ง `$fillable` หรือปิด `$guarded`(ดูตัวอย่างด้านล่าง)

**โครงสร้าง Model ตัวอย่าง**
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // ชื่อฟิลด์ที่อนุญาตให้กรอกแบบ mass assignment
    protected $fillable = ['name', 'price'];

    // แปลงชนิดข้อมูลอัตโนมัติ
    protected $casts = [
        'price' => 'decimal:2',
    ];

    // ถ้าตาราง/คีย์ไม่เป็นค่าเริ่มต้น
    // protected $table = 'my_products';
    // protected $primaryKey = 'product_id';
    // public $timestamps = false;

    // ความสัมพันธ์ (ตัวอย่าง)
    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot(['quantity', 'price']);
    }
}
```

***ทิปสำคัญ***
- ชื่อ Model เป็นเอกพจน์ (`Product`) → ตารางเป็นพหูพจน์ (`products`) โดยอัตโนมัติ

## Step 2 – Migration คืออะไร

**Migration** คือไฟล์ PHP ที่อธิบาย “โครงสร้างตาราง (Schema)” ของฐานข้อมูล  
ช่วยให้ทุกคนในทีมมีโครงสร้าง DB เหมือนกัน และสามารถ **เวอร์ชันคอนโทรล** ได้

---

**คำสั่งพื้นฐาน**
```bash
php artisan make:migration create_products_table   # สร้างไฟล์ migration
php artisan migrate                                # รัน migration ทั้งหมดที่ยังไม่เคยรัน
php artisan migrate:rollback                       # ย้อนกลับ batch ล่าสุด
php artisan migrate:fresh --seed                   # ล้างตาราง + migrate ใหม่ + seed
```
**โครงไฟล์ Migration (ตัวอย่าง)**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();                       // BIGINT auto increment
            $table->string('name');             // VARCHAR(255)
            $table->decimal('price', 10, 2);    // DECIMAL(10,2)
            $table->timestamps();               // created_at, updated_at
            // $table->softDeletes();           // deleted_at (ถ้าใช้ Soft Delete)
            // $table->unique(['name']);        // คีย์ไม่ซ้ำ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```
**เพิ่ม/แก้โครงสร้างตาราง (แก้ของเดิม)**
```bash
php artisan make:migration add_stock_to_products_table
```
```php
public function up(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->integer('stock')->default(0)->after('price');
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('stock');
    });
}
```

-  การแก้ชนิด/เปลี่ยนชื่อคอลัมน์ อาจต้องติดตั้ง `doctrine/dbal:`
```bash
  composer require doctrine/dbal
```

**Foreign Key / ดัชนี**
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->timestamps();

    // ดัชนีเพิ่มเติม
    // $table->index('customer_id');
});
```
**ทิปสั้น ๆ**

- เก็บไฟล์ migration ไว้ใน VCS (Git) → ทุกคนรัน php artisan migrate เพื่อซิงค์โครงสร้าง
- ใช้ timestamps() บ่อย ๆ เพื่อบันทึกเวลาเปลี่ยนแปลง
- ใช้ constrained()/foreignId() เพื่อสร้าง FK ง่ายและถูกต้อง
- ใช้ migrate:fresh เฉพาะตอนพัฒนา (ระวังข้อมูลหาย)

## Step 3 – รัน Migration

รันคำสั่ง:
```bash
php artisan migrate
```
- จากนั้นตรวจสอบตารางใน MySQL:ตาราง `products` จะถูกสร้างขึ้นพร้อมคอลัมน์ที่กำหนดใน Migration

## Step 4 – การใช้ Model จัดการข้อมูล

**เพิ่มข้อมูล**
```php
use App\Models\Product;

Route::get('/test-insert', function () {
    Product::create([
        'name' => 'ปากกา',
        'price' => 15,
        'stock' => 100
    ]);
    return "เพิ่มสินค้าเรียบร้อย";
});
```
⚠️ ต้องเปิดให้ Model อนุญาต mass assignment โดยแก้ไฟล์ `app/Models/Product.php:`
```php
protected $fillable = ['name', 'price', 'stock'];
```

**ดึงข้อมูล**
```php
Route::get('/test-all', function () {
    $products = Product::all();
    return $products; // Laravel จะส่งเป็น JSON อัตโนมัติ
});
```

**แก้ไขข้อมูล**
```php
Route::get('/test-update/{id}', function ($id) {
    $product = Product::findOrFail($id);
    $product->update(['price' => 20]);
    return "อัปเดตราคาแล้ว";
});
```

**ลบข้อมูล**
```php
Route::get('/test-delete/{id}', function ($id) {
    Product::destroy($id);
    return "ลบสินค้าสำเร็จ";
});
```

## Step 7 – Factory + Seeder (เสริม)

หากต้องการสร้าง **ข้อมูลทดสอบ (Dummy Data)** อย่างรวดเร็ว:

1. **สร้าง Factory**
```bash
php artisan make:factory ProductFactory --model=Product
```

2. **แก้ไฟล์ database/factories/ProductFactory.php**
```php
public function definition(): array
{
    return [
        'name' => fake()->word(),
        'price' => fake()->randomFloat(2, 5, 100),
        'stock' => fake()->numberBetween(0, 50)
    ];
}
```
3. **รัน Seeder:**
```php
Product::factory()->count(10)->create();
```

### Step 8 – Mini Workshop

**โจทย์:**  
1. สร้าง **Model + Migration** ชื่อ `Customer`  
2. คอลัมน์: `name (string)`, `email (string, unique)`, `phone (string, nullable)`
3. รัน Migration
4. เขียน Route ทดสอบ:

---
- ตอนนี้เราสามารถสร้างและจัดการตารางด้วย Laravel ได้แล้ว
- Section 11 เราจะเรียน Blade Template เพื่อแสดงข้อมูลจาก Model บนหน้าเว็บแบบสวยงาม
---

# Section 11 – Blade Template

🎯 **เป้าหมายการเรียนรู้**  

1. เข้าใจว่า **Blade Template** คืออะไร และทำไมต้องใช้
2. สร้างไฟล์ **View** ด้วย Blade ได้
3. ใช้ **Blade Syntax** แสดงข้อมูล, loop, เงื่อนไข
4. สร้าง **Layout หลัก** และใช้ `@section / @yield` เพื่อจัดโครงหน้าเว็บ

## Step 1 – Blade Template คืออะไร

**Blade** คือ Template Engine ของ Laravel  

- ใช้ไฟล์นามสกุล **`.blade.php`** ในโฟลเดอร์ `resources/views/`  

**ประโยชน์ของ Blade:**
- เขียนโค้ด PHP ใน HTML ได้ง่ายขึ้น
- ใช้ **Layout** ซ้ำได้
- มี **Syntax** สำหรับ `loop`, `if`, `include`  

## Step 2 – สร้าง View เบื้องต้น

1. **สร้างไฟล์:** `resources/views/hello.blade.php`

```blade
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Hello Page</title>
</head>
<body>
    <h1>สวัสดี {{ $name }}</h1>
    <p>วันนี้วันที่ {{ date('d/m/Y') }}</p>
</body>
</html>
```

2. **กำหนด Route เพื่อเรียก View**
```php
Route::get('/hello', function () {
    return view('hello', ['name' => 'สมชาย']);
});
```

## Step 3 – Blade Syntax พื้นฐาน

#### แสดงตัวแปร
```blade
{{ $variable }}       {{-- escape HTML --}}
{!! $variable !!}     {{-- แสดง HTML ตรง ๆ --}}
```

#### เงื่อนไข
```blade
@if($score >= 50)
    <p style="color:green;">ผ่าน</p>
@else
    <p style="color:red;">ไม่ผ่าน</p>
@endif
```

#### ลูป
```blade
@foreach($products as $p)
    <li>{{ $p->name }} - {{ number_format($p->price, 2) }} บาท</li>
@endforeach
```

## Step 4 – Layout และ Section

---

#### 1. Layout หลัก: `resources/views/layouts/main.blade.php`

```blade
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'My App')</title>
</head>
<body>
    <header>
        <h1>My Laravel App</h1>
        <hr>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <hr>
        <p>&copy; 2025 MyApp</p>
    </footer>
</body>
</html>
```
#### 2. หน้าใช้ Layout: `resources/views/products/index.blade.php`

```blade
@extends('layouts.main')

@section('title', 'รายการสินค้า')

@section('content')
    <h2>สินค้า</h2>
    <ul>
        @foreach($products as $p)
            <li>{{ $p->name }} - {{ number_format($p->price, 2) }} บาท</li>
        @endforeach
    </ul>
@endsection
```
#### 3. Controller/Route ส่งข้อมูล

```php
use App\Models\Product;

Route::get('/products', function () {
    $products = Product::all();
    return view('products.index', compact('products'));
});
```

## Step 5 – การ Include View ย่อย

ใช้ Blade เพื่อแยกโค้ดออกเป็น **Partial View** แล้วนำมาใช้ซ้ำได้

```blade
@include('partials.nav')
@includeWhen($isAdmin, 'partials.admin_menu')
```
- `@include('partials.nav')` → ดึงไฟล์ `resources/views/partials/nav.blade.php` มาแสดง
- `@includeWhen($isAdmin, 'partials.admin_menu')` → จะแสดง admin_menu ก็ต่อเมื่อ `$isAdmin` เป็นจริง

## Step 6 – Mini Workshop

**โจทย์:**  
สร้างหน้าเว็บ `customers/index.blade.php` ที่:

1. ใช้ **Layout หลัก** (`layouts.main`)
2. แสดงรายชื่อลูกค้าจาก **Model Customer**
3. ถ้าลูกค้ามีอีเมล ให้แสดงในวงเล็บหลังชื่อ  
  เช่น: `สมชาย ใจดี (somchai@example.com)`

**ขั้นตอน**
- เขียน Route และ Controller ดึงข้อมูลลูกค้า
- ส่งไป View
- ใช้ Blade Loop + เงื่อนไข แสดงข้อมูลตามโจทย์
