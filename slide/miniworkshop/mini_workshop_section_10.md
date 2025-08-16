#### 1. สร้าง Model + Migration
```bash
php artisan make:model Customer -m
```

#### 2. แก้ไขไฟล์ Migration (`database/migrations/xxxx_xx_xx_create_customers_table.php`)
```php
public function up(): void
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->timestamps();
    });
}
```

#### 3. รัน Migration
```bash
php artisan migrate
```

#### 4. แก้ไข Model (`app/Models/Customer.php`)
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone'];
}
```

#### 5. เขียน Route ทดสอบ (`routes/web.php`)
```php
use App\Models\Customer;

Route::get('/customer/add', function () {
    Customer::create([
        'name' => 'สมชาย ใจดี',
        'email' => 'somchai@example.com',
        'phone' => '0812345678'
    ]);
    return "เพิ่มลูกค้าสำเร็จ";
});

Route::get('/customer/list', function () {
    return Customer::all(); // ส่งออกเป็น JSON
});

Route::get('/customer/update/{id}', function ($id) {
    $customer = Customer::findOrFail($id);
    $customer->update(['phone' => '0999999999']);
    return "อัปเดตเบอร์โทรเรียบร้อย";
});

Route::get('/customer/delete/{id}', function ($id) {
    Customer::destroy($id);
    return "ลบลูกค้าสำเร็จ";
});
```