# Step 2 — ติดตั้ง Filament 3 + สร้างแผง Admin และผู้ใช้

## 2.1 ติดตั้งแพ็กเกจ Filament 3

รันในโฟลเดอร์โปรเจกต์ (จาก Step 1):

```bash
composer require filament/filament:"^3.3" -W
```

## 2.2 สร้าง Panel สำหรับแผงผู้ดูแล (Admin Panel)

Filament v3 ใช้แนวคิด “Panel” ให้สร้างด้วยคำสั่งนี้:

```bash
php artisan make:filament-panel admin
```

## 2.3 สร้างผู้ใช้สำหรับเข้า /admin

Filament มีคำสั่งช่วยสร้าง User ให้เร็ว:

```bash
php artisan make:filament-user
```

**กรอก:**

- **Name:** admin
- **Email:** admin@example.com
- **Password:** (ตั้งรหัส)
- **Confirm Password:** (ยืนยัน)

> คำสั่งนี้จะสร้างเรคคอร์ดในตาราง `users` ตาม `User` model ของ Laravel (guard `web`)  
ภายหลังเราจะมอบบทบาท/สิทธิ์ด้วย **Spatie** ใน Step RBAC

## 2.4 ทดสอบเข้าแผงผู้ดูแล

รันเซิร์ฟเวอร์:

```bash
php artisan serve
```

เปิดเบราว์เซอร์ไปที่:

```
http://127.0.0.1:8000/admin
```
ล็อกอินด้วยอีเมล/รหัสผ่านที่เพิ่งสร้าง → ควรเห็นหน้า Dashboard ของ Filament

# Step 3 — Models & Migrations + เตรียม CRUD

## 3.1 สร้าง Model + Migration

```bash
php artisan make:model EquipmentCategory -m
php artisan make:model Equipment -m
php artisan make:model Borrow -m
```
## 3.2 แก้ไขไฟล์ Migration

**ไฟล์:** `database/migrations/xxxx_xx_xx_create_equipment_categories_table.php`

```php
public function up(): void
{
    Schema::create('equipment_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });
}
```

**ไฟล์:** `database/migrations/xxxx_xx_xx_create_equipment_table.php`

```php
public function up(): void
{
    Schema::create('equipment', function (Blueprint $table) {
        $table->id();
        $table->foreignId('equipment_category_id')->constrained()->cascadeOnDelete();
        $table->string('code')->unique();     // รหัสอุปกรณ์
        $table->string('name');               // ชื่ออุปกรณ์
        $table->unsignedInteger('stock')->default(0);
        $table->string('photo_path')->nullable(); // เก็บพาธรูป (storage)
        $table->timestamps();

        $table->index('stock'); // ช่วย filter ในตาราง/รายงาน
    });
}
```

**ไฟล์:** `database/migrations/xxxx_xx_xx_create_borrows_table.php`

```php
public function up(): void
{
    Schema::create('borrows', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
        $table->timestamp('borrowed_at');
        $table->timestamp('due_at')->nullable();
        $table->timestamp('returned_at')->nullable();
        $table->string('status')->default('borrowed'); // borrowed|returned|overdue
        $table->string('note')->nullable();
        $table->timestamps();

        // ดัชนีที่ช่วยงานรายงาน/ค้นหา
        $table->index(['borrowed_at']);
        $table->index(['due_at']);
        $table->index(['returned_at']);
        $table->index(['user_id','equipment_id']);
        $table->index(['status']);
    });
}
```

ℹ️ ตอนนี้ยังไม่ใส่ SoftDeletes — ถ้าจะใช้สามารถเพิ่มใน Step หลัง ๆ ได้

## 3.3 Run Migrate

ใช้คำสั่งด้านล่างเพื่อสร้างตารางทั้งหมดในฐานข้อมูลตามที่กำหนดไว้

```bash
php artisan migrate
```

## 3.4 สร้างความสัมพันธ์ในโมเดล (Models)

**ไฟล์:** `app/Models/EquipmentCategory.php`

```php
class EquipmentCategory extends Model
{
    protected $fillable = ['name'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
```

**ไฟล์:** `app/Models/Equipment.php`

```php
class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'equipment_category_id','code','name','stock','photo_path',
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
}
```

**ไฟล์:** `app/Models/Borrow.php`

```php
class Borrow extends Model
{
    protected $fillable = [
        'user_id','equipment_id','borrowed_at','due_at','returned_at','status','note',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at'      => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    // ช่วย query เร็วๆ: รายการที่ยังไม่คืน
    public function scopeActive($q)
    {
        return $q->whereNull('returned_at');
    }
}
```

## 3.5 สร้าง Factory + Seeder ข้อมูลตัวอย่าง

เพื่อให้สามารถทดสอบใน **Step 4** ควรสร้าง Factory สำหรับแต่ละโมเดล

```bash
php artisan make:factory EquipmentCategoryFactory --model=EquipmentCategory
php artisan make:factory EquipmentFactory --model=Equipment
php artisan make:factory BorrowFactory --model=Borrow
```

**ไฟล์:** `database/factories/EquipmentCategoryFactory.php`

```php
public function definition(): array
{
    return [
        'name' => fake()->unique()->randomElement([
            'Laptop','Camera','Accessory','Tablet','Sensor'
        ]),
    ];
}
```

**ไฟล์:** `database/factories/EquipmentFactory.php`

```php
public function definition(): array
{
    return [
        'equipment_category_id' => \App\Models\EquipmentCategory::factory(),
        'code'  => strtoupper(fake()->bothify('EQ-###')),
        'name'  => fake()->randomElement([
            'ThinkPad E14','Acer Swift','Canon 80D','Tripod','Mic Set'
        ]),
        'stock' => fake()->numberBetween(0, 10),
        'photo_path' => null,
    ];
}
```

**ไฟล์:** `database/factories/BorrowFactory.php`

```php
public function definition(): array
{
    $borrowed = fake()->dateTimeBetween('-14 days','now');
    $due      = (clone $borrowed)->modify('+7 days');
    $returned = fake()->boolean(60) ? (clone $borrowed)->modify('+'.rand(1,9).' days') : null;

    return [
        'user_id'      => \App\Models\User::factory(), // เดี๋ยวเรามีแอดมินจาก Step 2 แล้วก็ใช้ id นั้นได้
        'equipment_id' => \App\Models\Equipment::factory(),
        'borrowed_at'  => $borrowed,
        'due_at'       => $due,
        'returned_at'  => $returned,
        'status'       => $returned ? 'returned' : 'borrowed',
        'note'         => fake()->optional()->sentence(6),
    ];
}
```

**ไฟล์:** `database/seeders/DatabaseSeeder.php` (เติมท้ายนิดเดียว)

```php
public function run(): void
{
    // 1) สร้างหมวด 5 อันก่อน (พอสำหรับอุปกรณ์หลายชิ้น)
    $cats = \App\Models\EquipmentCategory::factory()->count(5)->create();

    // 2) สร้างอุปกรณ์ 10 ชิ้น โดยสุ่มผูกกับหมวดที่มีอยู่
    \App\Models\Equipment::factory()
        ->count(10)
        ->state(fn () => ['equipment_category_id' => $cats->random()->id])
        ->create();

    // 3) สร้าง borrow ตัวอย่าง 8 รายการ ใช้ user ที่มีอยู่ (หรือสร้างใหม่ถ้าไม่มี)
    $userId = \App\Models\User::query()->inRandomOrder()->value('id')
        ?? \App\Models\User::factory()->create()->id;

    \App\Models\Borrow::factory()
        ->count(8)
        ->state(function () use ($userId) {
            return [
                'user_id'      => $userId,
                'equipment_id' => \App\Models\Equipment::inRandomOrder()->value('id'),
            ];
        })
        ->create();
}
```

## 3.6 เตรียม CRUD บน Filament 3 (สั่ง Generate โครงให้ก่อน)

คำสั่งนี้จะสร้าง Resource + Pages (List/Create/Edit) พร้อมสแกน schema ให้ฟอร์ม/ตารางเริ่มต้น:

```bash
php artisan make:filament-resource EquipmentCategory --generate
php artisan make:filament-resource Equipment --generate
php artisan make:filament-resource Borrow --generate
```
> ถ้าต้องการรองรับ SoftDeletes ให้เพิ่ม --soft-deletes ตอนสั่งคำสั่ง (ค่อยทำภายหลังได้)


## 3.7 ตรวจว่า Resource โผล่ในเมนู /admin

- เปิด `http://127.0.0.1:8000/admin` → เมนู **Equipment Categories / Equipment / Borrows** ควรปรากฏ
- เมนู Equipment Categories / Equipment / Borrows ควรขึ้นครบ
- ถ้าไม่ขึ้น ให้ตรวจ `config/filament.php` (path `/admin`), และ Policy (หากคุณสร้าง policy เอง ensure `viewAny()` คืน true)

# Step 4 — ปรับแต่ง Filament Resources

**โฟลเดอร์หลัก:** `app/Filament/Resources/*`  
ด้านล่างคือไฟล์ตัวอย่าง “พร้อมวางทับ” ได้เลย ถ้าใช้ `--generate` มาก่อน ให้แก้/เติมตามนี้

## 4.1 EquipmentCategoryResource

**คุณสมบัติ**
- ฟอร์ม: `name` (unique, max:100)
- ตาราง: `name`, จำนวนอุปกรณ์ในหมวดหมู่ (นับความสัมพันธ์), เวลาอัปเดต

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentCategoryResource\Pages;
use App\Models\EquipmentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Categories';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('ชื่อหมวดหมู่')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อหมวดหมู่')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment_count')
                    ->counts('equipment')
                    ->label('จำนวนอุปกรณ์')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดต')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEquipmentCategories::route('/'),
            'create' => Pages\CreateEquipmentCategory::route('/create'),
            'edit'   => Pages\EditEquipmentCategory::route('/{record}/edit'),
        ];
    }
}
```

## 4.2 EquipmentResource

**คุณสมบัติ**

- ฟอร์ม: เลือกหมวดหมู่ (relationship), `code` (unique), `name`, `stock` (≥0), อัปโหลดรูปไป `public/storage/equipment`
- ตาราง: รูป, รหัส, ชื่อ, หมวดหมู่, `stock` เป็น Badge สี, เวลาอัปเดต
- ฟิลเตอร์: เลือกตามหมวดหมู่, “สต็อก ≤ 2”, มี/ไม่มีรูป

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EquipmentCategoryResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationLabel = 'Equipment';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('equipment_category_id')
                ->relationship('category', 'name')
                ->label('หมวดหมู่')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('code')
                ->label('รหัส')
                ->required()
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('name')
                ->label('ชื่ออุปกรณ์')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('stock')
                ->label('สต็อก')
                ->numeric()
                ->minValue(0)
                ->required(),

            Forms\Components\FileUpload::make('photo_path')
                ->label('รูป')
                ->image()
                ->directory('equipment')
                ->disk('public')
                ->imagePreviewHeight('150')
                ->openable()
                ->downloadable(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->disk('public')
                    ->square()
                    ->label('รูป'),

                Tables\Columns\TextColumn::make('code')
                    ->label('รหัส')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('หมวด')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('stock')
                    ->label('คงเหลือ')
                    ->sortable()
                    ->colors([
                        'danger'  => fn ($state) => $state <= 0,
                        'warning' => fn ($state) => $state > 0 && $state <= 2,
                        'success' => fn ($state) => $state > 2,
                    ]),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดต')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('equipment_category_id')
                    ->label('หมวดหมู่')
                    ->relationship('category', 'name')
                    ->preload(),

                Tables\Filters\Filter::make('low_stock')
                    ->label('สต็อก ≤ 2')
                    ->query(fn ($q) => $q->where('stock', '<=', 2)),

                Tables\Filters\TernaryFilter::make('has_photo')
                    ->label('มีรูป')
                    ->nullable()
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('photo_path'),
                        false: fn ($q) => $q->whereNull('photo_path'),
                        blank: fn ($q) => $q
                    ),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit'   => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
```

## 4.3 BorrowResource

**คุณสมบัติ**

- ฟอร์ม: เลือกผู้ยืม, เลือกอุปกรณ์ (โชว์ `code — name`), `borrowed_at` (default: now), `due_at` (default: +7 วัน), `note`
- ตาราง: ผู้ยืม, รหัส–ชื่ออุปกรณ์, วันยืม/กำหนดคืน/วันคืน, สถานะ (Badge)
- ฟิลเตอร์: ช่วงวันที่ยืม, สถานะ
- แอ็กชัน: **Return** — ใช้ Transaction + `lockForUpdate()` เพื่อกันแข่งกัน/กดซ้ำ

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowResource\Pages;
use App\Models\Borrow;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class BorrowResource extends Resource
{
    protected static ?string $model = Borrow::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Borrows';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->label('ผู้ยืม')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('equipment_id')
                ->relationship('equipment', 'name')
                ->getOptionLabelFromRecordUsing(fn (Equipment $e) => "{$e->code} — {$e->name}")
                ->searchable()
                ->preload()
                ->required()
                ->helperText('ยืมได้เฉพาะอุปกรณ์ที่มีสต็อก > 0'),

            Forms\Components\DateTimePicker::make('borrowed_at')
                ->label('ยืมเมื่อ')
                ->seconds(false)
                ->default(now())
                ->required(),

            Forms\Components\DateTimePicker::make('due_at')
                ->label('กำหนดคืน')
                ->seconds(false)
                ->default(now()->addDays(7)),

            Forms\Components\TextInput::make('note')
                ->label('หมายเหตุ'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('ผู้ยืม')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('equipment.code')
                    ->label('รหัส')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('อุปกรณ์')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('borrowed_at')
                    ->label('ยืมเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('กำหนดคืน')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('returned_at')
                    ->label('คืนเมื่อ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors([
                        'warning' => 'borrowed',
                        'success' => 'returned',
                        'danger'  => 'overdue',
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('period')
                    ->label('ช่วงวันที่ยืม')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('จาก'),
                        Forms\Components\DatePicker::make('to')->label('ถึง'),
                    ])
                    ->query(function ($query, array $data) {
                        $from = $data['from'] ?? null;
                        $to   = $data['to'] ?? null;
                        if ($from) $query->whereDate('borrowed_at', '>=', $from);
                        if ($to)   $query->whereDate('borrowed_at', '<=', $to);
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'borrowed' => 'ยืมอยู่',
                        'returned' => 'คืนแล้ว',
                        'overdue'  => 'เกินกำหนด',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('return')
                    ->label('คืน')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->visible(fn (Borrow $record) => $record->returned_at === null)
                    ->action(function (Borrow $record) {
                        DB::transaction(function () use ($record) {
                            $record->refresh(); // กันข้อมูลค้าง
                            if ($record->returned_at) return;

                            // อัปเดตสถานะคืน
                            $record->update([
                                'returned_at' => now(),
                                'status'      => 'returned',
                            ]);

                            // คืนสต็อก (ล็อกแถวป้องกันแข่ง)
                            $eq = Equipment::lockForUpdate()->find($record->equipment_id);
                            if ($eq) $eq->increment('stock');
                        });
                    }),
            ])
            ->defaultSort('borrowed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBorrows::route('/'),
            'create' => Pages\CreateBorrow::route('/create'),
            'edit'   => Pages\EditBorrow::route('/{record}/edit'),
        ];
    }
}
```

> หมายเหตุ: “เช็คสต็อก > 0 ก่อนยืม” และ “ห้ามผู้ใช้เดิมยืมซ้ำอุปกรณ์ชิ้นเดิมถ้ายังไม่คืน” จะทำใน Step 5

### ลองทดสอบ (หลังแก้ไฟล์)

1. เข้า `/admin/equipment`
    - สร้าง/แก้/อัปโหลดรูป
    - ✅ ตารางต้องมี **Badge สี** ตามค่าคงเหลือ `stock`

2. เข้า `/admin/borrows`
    - สร้างรายการยืม
    - ✅ ตารางต้องขึ้นรายการที่สร้างใหม่

3. กด **Return** ในรายการที่ยังไม่คืน
    - ✅ `returned_at` ถูกตั้งค่า (เวลาปัจจุบัน)
    - ✅ `stock` ของอุปกรณ์นั้น **+1**

# Step 5 — Business Rules (Borrow) + RBAC (Filament 3)

## 5.1 ติดตั้ง RBAC (Spatie Permission)

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```
