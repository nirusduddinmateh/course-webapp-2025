#### 1. สร้าง Controller
ใช้ Artisan คำสั่ง:
```bash
php artisan make:controller CustomerController
```
- Laravel จะสร้างไฟล์:
`app/Http/Controllers/CustomerController.php`

#### 2. เขียนโค้ดใน Controller
```php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();   // ดึงข้อมูลลูกค้าทั้งหมด
        return view('customers.index', compact('customers'));
    }
}
```

#### 1. เขียน Route และ Controller ดึงข้อมูลลูกค้า
ไฟล์: `routes/web.php`
```php
use App\Http\Controllers\CustomerController;

Route::get('/customers', [CustomerController::class, 'index']);
```

#### 2. View: `resources/views/customers/index.blade.php`
```php
@extends('layouts.main')

@section('title', 'รายชื่อลูกค้า')

@section('content')
    <h2>รายชื่อลูกค้า</h2>
    <ul>
        @foreach($customers as $c)
            <li>
                {{ $c->name }}
                @if($c->email)
                    ({{ $c->email }})
                @endif
            </li>
        @endforeach
    </ul>
@endsection
```

#### 2. View: `resources/views/customers/index.blade.php`
```php
@extends('layouts.main')

@section('title', 'รายชื่อลูกค้า')

@section('content')
    <h2>รายชื่อลูกค้า</h2>
    <ul>
        @foreach($customers as $c)
            <li>
                {{ $c->name }}
                @if($c->email)
                    ({{ $c->email }})
                @endif
            </li>
        @endforeach
    </ul>
@endsection
```

📌 เมื่อเปิด http://127.0.0.1:8000/customers ระบบจะเรียก CustomerController@index ดึงข้อมูลลูกค้าจาก Model และแสดงผลใน View