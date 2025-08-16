1. **สร้าง Controller**
```bash
php artisan make:controller ProductController
```

2. **เขียนเมธอดใน Controller**

ไฟล์: `app/Http/Controllers/ProductController.php`

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return "รายการสินค้า";
    }

    public function show($id)
    {
        return "รายละเอียดสินค้ารหัส: $id";
    }

    public function create()
    {
        return "เพิ่มสินค้าใหม่";
    }
}
```

3. **กำหนด Route Group**

ไฟล์: `routes/web.php`

```php
use App\Http\Controllers\ProductController;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/create', [ProductController::class, 'create']);
    Route::get('/{id}', [ProductController::class, 'show']);
});
```