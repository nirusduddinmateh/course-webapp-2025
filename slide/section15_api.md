# Section 15 – สร้าง API Service

## Step 1 - ติดตั้ง & ตั้งค่า JWT

แนะนำใช้แพ็กเกจ **`php-open-source-saver/jwt-auth`** (fork ต่อจาก tymon) ซึ่งรองรับ **Laravel 11** ได้ดี

**คำสั่งติดตั้งและตั้งค่า**
```bash
composer require php-open-source-saver/jwt-auth
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

คำสั่งด้านบนจะ:
- ติดตั้งแพ็กเกจ JWT Auth
- publish ไฟล์คอนฟิก `config/jwt.php`
- สร้างค่า `JWT_SECRET` ในไฟล์ `.env`

แก้ `config/auth.php` เพิ่ม guard `api` ให้ใช้ driver `jwt`:

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'api' => ['driver' => 'jwt', 'provider' => 'users'], // <—-----
],
```

ตั้งค่าหมดอายุ `token` ใน `config/jwt.php` (ตัวอย่าง 60 นาที):

```php
'ttl' => env('JWT_TTL', 60),
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 14 วัน
```

## Step 2 - สร้าง AuthController (login/logout/refresh/me)

```bash
php artisan make:controller Api/AuthController
```

**ไฟล์:**  `app/Http/Controllers/Api/AuthController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (! $token = auth('api')->attempt($cred)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    public function logout()
    {
        auth('api')->logout(); // blacklist current token (ถ้าเปิด blacklisting)
        return response()->json(['message' => 'Logged out']);
    }

    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // seconds
            'user'         => auth('api')->user(),
        ]);
    }
}
```

## Step 3 - กำหนดเส้นทาง API (v1) + CORS

**ไฟล์:** `routes/api.php`

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EquipmentCategoryController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\BorrowController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Categories
        Route::apiResource('categories', EquipmentCategoryController::class);

        // Equipment
        Route::apiResource('equipment', EquipmentController::class);

        // Borrow / Return
        Route::post('borrows', [BorrowController::class, 'store']);
        Route::post('borrows/{borrow}/return', [BorrowController::class, 'return']);
    });
});
```

**ตั้งค่า CORS (config/cors.php)**

เพื่อให้ Mobile App หรือ frontend ที่มาจาก domain อื่น (เช่น Angular/Ionic) เรียก API ได้
แก้ให้ `paths` ครอบคลุม `api/*` และอนุญาต Header `Authorization`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // หรือกำหนดเป็นโดเมนเฉพาะ เช่น ['https://myapp.com']

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
```

## Step 4 - สร้าง Request/Resource/Controller: หมวดหมู่

```bash
php artisan make:request Api/CategoryStoreRequest
php artisan make:request Api/CategoryUpdateRequest
php artisan make:resource Api/CategoryResource
php artisan make:controller Api/EquipmentCategoryController --api
```

**ไฟล์:** `app/Http/Requests/Api/CategoryStoreRequest.php`

```php
public function rules(): array {
  return ['name' => ['required','string','max:100','unique:equipment_categories,name']];
}
```

**ไฟล์:** `app/Http/Requests/Api/CategoryUpdateRequest.php`

```php
public function rules(): array {
  $id = $this->route('category'); // จาก route model binding
  return ['name' => ['required','string','max:100',"unique:equipment_categories,name,{$id}"]];
}
```

**ไฟล์:** `app/Http/Resources/Api/CategoryResource.php`

```php
public function toArray($request): array {
  return ['id'=>$this->id,'name'=>$this->name,'updated_at'=>$this->updated_at?->toISOString()];
}
```

**ไฟล์:** `app/Http/Controllers/Api/EquipmentCategoryController.php`

```php
public function __construct() { 
    $this->middleware('auth:api'); 
}

public function index() {
  $data = \App\Models\EquipmentCategory::query()->latest('updated_at')->paginate(10);
  return \App\Http\Resources\Api\CategoryResource::collection($data);
}

public function store(\App\Http\Requests\Api\CategoryStoreRequest $request) {
  $cat = \App\Models\EquipmentCategory::create($request->validated());
  return new \App\Http\Resources\Api\CategoryResource($cat);
}

public function show(\App\Models\EquipmentCategory $category) {
  return new \App\Http\Resources\Api\CategoryResource($category);
}

public function update(\App\Http\Requests\Api\CategoryUpdateRequest $request, \App\Models\EquipmentCategory $category) {
  $category->update($request->validated());
  return new \App\Http\Resources\Api\CategoryResource($category);
}

public function destroy(\App\Models\EquipmentCategory $category) {
  $category->delete();
  return response()->noContent();
}
```

## Step 5 - สร้าง Request/Resource/Controller: อุปกรณ์

```bash
php artisan make:request Api/EquipmentStoreRequest
php artisan make:request Api/EquipmentUpdateRequest
php artisan make:resource Api/EquipmentResource
php artisan make:controller Api/EquipmentController --api
```

**ไฟล์:** `app/Http/Requests/Api/EquipmentStoreRequest.php`

```php
public function rules(): array {
  return [
    'equipment_category_id' => ['required','exists:equipment_categories,id'],
    'code'  => ['required','string','max:50','unique:equipment,code'],
    'name'  => ['required','string','max:100'],
    'stock' => ['required','integer','min:0'],
    'photo' => ['nullable','file','image','max:2048'], // multipart
  ];
}
```

**ไฟล์:** `app/Http/Requests/Api/EquipmentUpdateRequest.php`

```php
public function rules(): array {
  $id = $this->route('equipment')->id;
  return [
    'equipment_category_id' => ['sometimes','exists:equipment_categories,id'],
    'code'  => ['sometimes','string','max:50',"unique:equipment,code,{$id}"],
    'name'  => ['sometimes','string','max:100'],
    'stock' => ['sometimes','integer','min:0'],
    'photo' => ['nullable','file','image','max:2048'],
  ];
}
```

**ไฟล์:** `app/Http/Resources/Api/EquipmentResource.php`

```php
public function toArray($request): array {
  return [
    'id'    => $this->id,
    'category' => ['id'=>$this->category->id,'name'=>$this->category->name],
    'code'  => $this->code,
    'name'  => $this->name,
    'stock' => (int) $this->stock,
    'photo_url' => $this->photo_path ? asset('storage/'.$this->photo_path) : null,
    'updated_at' => $this->updated_at?->toISOString(),
  ];
}
```

**ไฟล์:** `app/Http/Controllers/Api/EquipmentController.php`

```php
public function index() {
  $q = \App\Models\Equipment::with('category')->latest('updated_at');

  if ($s = request('search')) {
     $q->where(fn($qq)=> $qq->where('code','like',"%$s%")->orWhere('name','like',"%$s%"));
  }
  if ($cat = request('category_id')) $q->where('equipment_category_id',$cat);

  return \App\Http\Resources\Api\EquipmentResource::collection($q->paginate(10));
}

public function store(\App\Http\Requests\Api\EquipmentStoreRequest $request) {
  $data = $request->validated();
  if ($request->hasFile('photo')) {
      $data['photo_path'] = $request->file('photo')->store('equipment','public');
  }
  $e = \App\Models\Equipment::create($data);
  return new \App\Http\Resources\Api\EquipmentResource($e->load('category'));
}

public function show(\App\Models\Equipment $equipment) {
  return new \App\Http\Resources\Api\EquipmentResource($equipment->load('category'));
}

public function update(\App\Http\Requests\Api\EquipmentUpdateRequest $request, \App\Models\Equipment $equipment) {
  $data = $request->validated();
  if ($request->hasFile('photo')) {
      $data['photo_path'] = $request->file('photo')->store('equipment','public');
  }
  $equipment->update($data);
  return new \App\Http\Resources\Api\EquipmentResource($equipment->load('category'));
}

public function destroy(\App\Models\Equipment $equipment) {
  $equipment->delete();
  return response()->noContent();
}
```

> อัปโหลดรูปจาก Mobile: ส่งเป็น multipart/form-data ฟิลด์ชื่อ `photo`



## Step 6 - Endpoint บันทึกการยืม (ธุรกรรม + lock + กติกา)

```bash
php artisan make:request Api/BorrowStoreRequest
php artisan make:resource Api/BorrowResource
php artisan make:controller Api/BorrowController
```

**ไฟล์:** `app/Http/Requests/Api/BorrowStoreRequest.php`

```php
public function rules(): array {
  return [
    'equipment_id' => ['required','exists:equipment,id'],
    'due_at'       => ['nullable','date'], // ถ้าไม่ส่ง จะ +7 วันใน controller
    'note'         => ['nullable','string','max:255'],
  ];
}
```

**ไฟล์:** `app/Http/Resources/Api/BorrowResource.php`

```php
public function toArray($request): array {
  return [
    'id' => $this->id,
    'user' => ['id'=>$this->user->id,'name'=>$this->user->name],
    'equipment' => [
        'id'=>$this->equipment->id,'code'=>$this->equipment->code,'name'=>$this->equipment->name
    ],
    'borrowed_at' => $this->borrowed_at?->toISOString(),
    'due_at'      => $this->due_at?->toISOString(),
    'returned_at' => $this->returned_at?->toISOString(),
    'status'      => $this->status,
    'note'        => $this->note,
  ];
}
```

**ไฟล์:** `app/Http/Controllers/Api/BorrowController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BorrowStoreRequest;
use App\Http\Resources\Api\BorrowResource;
use App\Models\Borrow;
use App\Models\Equipment;
use Illuminate\Support\Facades\DB;

class BorrowController extends Controller
{
    public function store(BorrowStoreRequest $request)
    {
        $userId = auth('api')->id();
        $data = $request->validated();

        return DB::transaction(function () use ($data, $userId) {

            // 1) เช็คห้ามยืมซ้ำชิ้นเดิมถ้ายังไม่คืน
            $exists = Borrow::whereNull('returned_at')
                ->where('user_id', $userId)
                ->where('equipment_id', $data['equipment_id'])
                ->exists();
            if ($exists) {
                return response()->json(['message'=>'คุณค้างยืมอุปกรณ์ชิ้นนี้อยู่แล้ว'], 422);
            }

            // 2) ล็อกแถวอุปกรณ์ แล้วเช็ค stock
            /** @var Equipment $eq */
            $eq = Equipment::lockForUpdate()->findOrFail($data['equipment_id']);
            if ($eq->stock <= 0) {
                return response()->json(['message'=>'สต็อกไม่พอ'], 422);
            }

            // 3) สร้างรายการ + หัก stock
            $eq->decrement('stock');

            $borrow = Borrow::create([
                'user_id'      => $userId,
                'equipment_id' => $eq->id,
                'borrowed_at'  => now(),
                'due_at'       => $data['due_at'] ?? now()->addDays(7),
                'status'       => 'borrowed',
                'note'         => $data['note'] ?? null,
            ]);

            return (new BorrowResource($borrow->load(['user','equipment'])))
                    ->response()
                    ->setStatusCode(201);
        });
    }

    public function return(\App\Models\Borrow $borrow): JsonResponse
    {
        // เช็คก่อน: ถ้าคืนแล้ว ไม่ควรทำซ้ำ
        if ($borrow->returned_at) {
            return response()->json(['message' => 'รายการนี้คืนแล้ว'], 409);
        }

        return DB::transaction(function () use ($borrow) {
            // ล็อกแถวอุปกรณ์ป้องกันแข่งกัน
            $eq = \App\Models\Equipment::lockForUpdate()->findOrFail($borrow->equipment_id);

            // อัปเดตสถานะคืน
            $borrow->update([
                'returned_at' => now(),
                'status'      => 'returned',
            ]);

            // คืนสต็อก
            $eq->increment('stock');

            return (new \App\Http\Resources\Api\BorrowResource(
                $borrow->fresh()->load(['user','equipment'])
            ))->response()->setStatusCode(200);
        });
    }
}
```