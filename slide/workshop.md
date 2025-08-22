# ER Diagram

[dbdiagram.io](https://dbdiagram.io/d/ER_Workshop-62ccef7dcc1bc14cc59bf545)

# Step 1 — ติดตั้งโปรเจกต์ & ตั้งค่าพื้นฐาน (Laravel 11)

**เป้าหมาย:**  
ได้โปรเจกต์ Laravel 11 ใหม่, ต่อฐานข้อมูลได้, รันหน้าเว็บได้, พร้อมสำหรับติดตั้ง Filament/RBAC ในขั้นถัดไป

---

## 1.1 ตรวจเครื่องมือที่ต้องมี

เปิด Terminal / PowerShell แล้วเช็คเวอร์ชัน:

```bash
php -v          # ต้องเป็น PHP 8.2 ขึ้นไป
composer -V     # Composer 2.x
mysql --version  # หรือ mariadb --version
```

PHP ควรเปิด extension: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`
(บน Windows ใช้ XAMPP/Laragon, บน macOS ใช้ Homebrew + php)

## 1.2 สร้างโปรเจกต์ Laravel 11 ใหม่

เปิด Terminal / PowerShell แล้วรันคำสั่ง:

```bash
composer create-project laravel/laravel borrow-app "11.*"
cd borrow-app
```

## 1.3 ตั้งค่า `.env`

คัดลอกไฟล์ตัวอย่างและใส่ค่าแอป/ฐานข้อมูล

```bash
cp .env.example .env        # Windows ใช้: copy .env.example .env

# สร้าง App Key
php artisan key:generate
```

เปิด `.env` แล้วแก้ไข:
```env
APP_NAME="Borrow System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# แนะนำตั้ง timezone ให้ตรง (Laravel 11 ส่วนใหญ่รองรับผ่าน ENV นี้)
APP_TIMEZONE=Asia/Bangkok

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=borrow_system
DB_USERNAME=root
DB_PASSWORD=
```

## 1.4 สร้าง storage link

(สำหรับอัปโหลดรูปอุปกรณ์ในภายหลัง)

```bash
php artisan storage:link
```

## 1.5 Run Test (รันเซิร์ฟเวอร์เพื่อดูหน้าแรก)

รันเซิร์ฟเวอร์ของ Laravel:

```bash
php artisan serve
```

## 1.6 เพิ่ม Health Route ง่ายๆ (เช็คว่าแอปรัน)

แก้ไขไฟล์ `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'time' => now()->toDateTimeString(),
        'tz' => config('app.timezone'),
    ]);
});
```

## 1.7 (ทางเลือก) Setup Git Repository

รันคำสั่งในโฟลเดอร์โปรเจกต์:

```bash
git init
git add .
git commit -m "chore: Borrow System"
```

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

### เพิ่ม Trait ใน User **ไฟล์:** `app/Models/User.php`

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; // <---
    // ...
}
```

### สร้าง Seeder ตั้งค่า Roles/Permissions

```bash
php artisan make:seeder RbacSeeder
```
**ไฟล์:** `database/seeders/RbacSeeder.php`
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            'view admin',

            // categories
            'view equipment_categories','create equipment_categories','edit equipment_categories','delete equipment_categories',

            // equipment
            'view equipment','create equipment','edit equipment','delete equipment',

            // borrows
            'view borrows','create borrows','edit borrows','delete borrows','return borrows',

            // reports (ไว้ใช้ Step รายงาน)
            'view reports','export borrows',

            // Users
            'view users','create users','edit users','delete users',
            'assign roles','assign permissions','reset user passwords',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin  = Role::firstOrCreate(['name' => 'Admin',  'guard_name' => 'web']);
        $staff  = Role::firstOrCreate(['name' => 'Staff',  'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);

        // Admin: all
        $admin->syncPermissions(Permission::all());

        // Staff: ทำงานหลัก (ไม่มี delete/export)
        $staff->syncPermissions([
            'view admin',
            'view equipment_categories','create equipment_categories','edit equipment_categories',
            'view equipment','create equipment','edit equipment',
            'view borrows','create borrows','edit borrows','return borrows',
            'view reports',
        ]);

        // Viewer: ดูอย่างเดียว
        $viewer->syncPermissions([
            'view admin',
            'view equipment_categories','view equipment','view borrows','view reports',
        ]);

    }
}
```

**รัน seeder:**

```bash
php artisan db:seed --class=RbacSeeder
```

**ให้บทบาทกับผู้ใช้ (เช่น `admin@example.com` ):**

```bash
php artisan tinker
```

```php
>>> $u = \App\Models\User::where('email','admin@example.com')->first();
>>> $u->assignRole('Admin');           // หรือ 'Staff' / 'Viewer'
```

## 5.2 กันเข้า /admin ด้วย middleware สิทธิ์ `view admin`

สร้าง middleware:

```bash
php artisan make:middleware EnsureUserCanViewAdmin
```

**ไฟล์:** `app/Http/Middleware/EnsureUserCanViewAdmin.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserCanViewAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // ปล่อยให้ guest ไปเจอ auth middleware ของ Filament เอง
        if ($user && ! $user->can('view admin')) {
            abort(403, 'You do not have permission to view the admin panel.');
        }

        return $next($request);
    }
}
```

**ลงทะเบียน middleware:**

**ไฟล์:** `bootstrap/app.php`

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ลงทะเบียน alias สำหรับ middleware ของเรา
        $middleware->alias([
            'can.view.admin' => \App\Http\Middleware\EnsureUserCanViewAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

จากนั้นผูก middleware เข้ากับ Filament Panel (ให้ auth ทำงานก่อน แล้วค่อยเช็คสิทธิ์)

**ไฟล์:** `app/Providers/Filament/AdminPanelProvider.php`

```php
<?php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        // ...
        ->authMiddleware([
            'auth',
            'can.view.admin', // ใช้ alias ที่ลงทะเบียนไว้
        ]);
}
```

## 5.3 คุมปุ่ม/เมนูใน Resource ตามสิทธิ์

**EquipmentCategoryResource**

```php
public static function shouldRegisterNavigation(): bool
{
    return auth()->check() && auth()->user()->can('view equipment_categories');
}

public static function canViewAny(): bool
{
    return auth()->user()->can('view equipment_categories');
}

public static function canCreate(): bool
{
    return auth()->user()->can('create equipment_categories');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('edit equipment_categories');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('delete equipment_categories');
}
```

**EquipmentResource**

```php
public static function shouldRegisterNavigation(): bool
{
    return auth()->check() && auth()->user()->can('view equipment');
}

public static function canViewAny(): bool
{
    return auth()->user()->can('view equipment');
}

public static function canCreate(): bool
{
    return auth()->user()->can('create equipment');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('edit equipment');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('delete equipment');
}
```

**BorrowResource**

```php
public static function shouldRegisterNavigation(): bool
{
    return auth()->check() && auth()->user()->can('view borrows');
}

public static function canViewAny(): bool
{
    return auth()->user()->can('view borrows');
}

public static function canCreate(): bool
{
    return auth()->user()->can('create borrows');
}

public static function canEdit($record): bool
{
    return auth()->user()->can('edit borrows');
}

public static function canDelete($record): bool
{
    return auth()->user()->can('delete borrows');
}
```

ปุ่ม **Return** (ใน Step 4) ทำการเพิ่มเงื่อนไขสิทธิ์:

```php
->visible(fn ($record) => $record->returned_at === null && auth()->user()->can('return borrows'))
```

## 5.4 ใส่ Business Rules ตอน “สร้าง Borrow”

แก้ไฟล์ Create Page ของ Borrow เพื่อ:
- ป้องกัน “ยืมซ้ำชิ้นเดิม” (ถ้ายังไม่คืน)
- เช็คสต็อก > 0 ด้วยการ ล็อกแถว และหักสต็อกภายใน transaction

เปิดไฟล์: `app/Filament/Resources/BorrowResource/Pages/CreateBorrow.php`

แทนที่/เติมโค้ดหลัก ๆ (ถ้ายังไม่มีเมธอดพวกนี้ ให้สร้างเพิ่ม):

```php
<?php

namespace App\Filament\Admin\Resources\BorrowResource\Pages;

use App\Filament\Admin\Resources\BorrowResource;
use App\Models\Borrow;
use App\Models\Equipment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateBorrow extends CreateRecord
{
    protected static string $resource = BorrowResource::class;

    /** ตรวจสอบก่อนบันทึก (validate ธุรกิจ) */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1) ห้ามผู้ใช้เดิมยืม "อุปกรณ์ชิ้นเดิม" ซ้ำถ้ายังไม่คืน
        $exists = Borrow::query()
            ->whereNull('returned_at')
            ->where('user_id', $data['user_id'])
            ->where('equipment_id', $data['equipment_id'])
            ->exists();

        if ($exists) {
            $this->notify('danger', 'ผู้ใช้นี้ยืมอุปกรณ์ชิ้นนี้ค้างอยู่แล้ว');
            $this->halt(); // ยกเลิกการสร้าง
        }

        return $data;
    }

    /** สร้างเรคคอร์ดภายใต้ transaction + lock */
    protected function handleRecordCreation(array $data): Borrow
    {
        return DB::transaction(function () use ($data) {

            // 2) ล็อกแถวอุปกรณ์ (กันแข่งกัน) แล้วเช็คสต็อก
            /** @var Equipment $eq */
            $eq = Equipment::lockForUpdate()->findOrFail($data['equipment_id']);

            if ($eq->stock <= 0) {
                $this->notify('danger', 'สต็อกไม่พอ');
                $this->halt(); // ป้องกันกลับไปสร้างต่อ
            }

            // 3) หักสต็อก แล้วตั้งสถานะ
            $eq->decrement('stock');
            $data['status'] = 'borrowed';

            return Borrow::create($data);
        });
    }
}
```

### ข้อดี

- ใช้ `mutateFormDataBeforeCreate()` เพื่อตรวจ “ยืมซ้ำ” ให้หยุดตั้งแต่ก่อน insert  
- ใช้ `handleRecordCreation()` ครอบด้วย transaction และ `lockForUpdate()` จึงปลอดภัยต่อการกดพร้อมกันหลายคน  
- ถ้าอยากซ่อนรายการ “อุปกรณ์สต็อก 0” จาก dropdown ก็เพิ่ม query filter ได้ (ดู 5.5)


## 5.5 (ออปชัน) ซ่อนอุปกรณ์ที่สต็อก 0 จากตัวเลือก

ไปที่ `BorrowResource::form()` ส่วน `Select::make('equipment_id')` แล้วเพิ่ม query เงื่อนไข:

```php
Forms\Components\Select::make('equipment_id')
    ->relationship('equipment', 'name', modifyQueryUsing: fn ($q) => $q->where('stock', '>', 0))
    ->getOptionLabelFromRecordUsing(fn (\App\Models\Equipment $e) => "{$e->code} — {$e->name}")
    ->searchable()
    ->preload()
    ->required()
    ->helperText('เลือกเฉพาะอุปกรณ์ที่มีสต็อก > 0');
```
ถึงแม้จะซ่อนใน UI แล้ว เรายัง ต้องมี เช็คใน CreateBorrow เหมือนข้อ 5.4 เพื่อกันยิง API ตรง/แข่งกัน

## 5.6 ทดสอบ

1. ลองสร้าง **Borrow** ให้ผู้ใช้ *ยืมซ้ำชิ้นเดิม* → ❌ ควรถูกบล็อก
2. สร้าง **Borrow** จน `stock` เหลือ `0` แล้วลองยืมอีก → ❌ ควรบล็อก
3. เปิด **สองหน้าต่าง** สร้างรายการของอุปกรณ์เดียวกันพร้อมกัน → ✅ จะสำเร็จได้เพียง **หนึ่งรายการ** (เพราะ `lockForUpdate()` + transaction)
4. ตรวจปุ่ม **Return** ต้องเห็นเฉพาะผู้มีสิทธิ์ `return borrows` และเมื่อกดแล้ว → ✅ `stock` ของอุปกรณ์ **+1**

# Step 6 — User Management (Filament 3 + Spatie Permission)

เป้าหมาย
- มีหน้า **Users** ใน `/admin` เพื่อ: สร้าง/แก้ไข/ลบผู้ใช้
- กำหนด **Roles / Permissions** ให้ผู้ใช้
- **รีเซ็ตรหัสผ่าน** ได้ (เฉพาะผู้มีสิทธิ์)
- กัน **ลบตัวเอง** และซ่อนเมนู/ปุ่มตามสิทธิ์

## 6.1 อัปเดต Seeder ให้มีสิทธิ์เกี่ยวกับผู้ใช้

**ไฟล์:** `database/seeders/RbacSeeder.php` (เติมเพิ่มจากเดิม)

```php
$perms = [
    // ...

    // Users
    'view users','create users','edit users','delete users', 'assign roles','assign permissions','reset user passwords',
];
```

**รัน:**

```bash
php artisan db:seed --class=RbacSeeder
```

## 6.2 สร้าง Filament Resource: `UserResource`
```bash
php artisan make:filament-resource User --generate
```

คำสั่งนี้จะสร้างไฟล์:
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/UserResource/Pages/{List,Create,Edit}Users.php`

แก้ **`UserResource.php`** ให้เป็นแบบด้านล่าง (โค้ดเต็ม ใช้แทนได้เลย):

```php
<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationLabel = 'Users';

    /* -------- RBAC: ซ่อนเมนูถ้าไม่มีสิทธิ์ -------- */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view users');
    }

    /* -------- RBAC: คุม CRUD -------- */
    public static function canViewAny(): bool      { return auth()->user()->can('view users'); }
    public static function canCreate(): bool       { return auth()->user()->can('create users'); }
    public static function canEdit($record): bool  { return auth()->user()->can('edit users'); }
    public static function canDelete($record): bool
    {
        if ($record && $record->id === auth()->id()) return false; // ห้ามลบตัวเอง
        return auth()->user()->can('delete users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลผู้ใช้')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('ชื่อ')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('email')
                    ->label('อีเมล')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
            ])->columns(2),

            Forms\Components\Section::make('รหัสผ่าน')->schema([
                Forms\Components\TextInput::make('password')
                    ->label('รหัสผ่าน')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->rule(Password::defaults()),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('ยืนยันรหัสผ่าน')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->same('password'),
            ])->columns(2)
              ->visible(fn () => auth()->user()->can('reset user passwords')
                    || request()->routeIs('filament.resources.users.create')),

            Forms\Components\Section::make('บทบาท/สิทธิ์')->schema([
                Forms\Components\Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->preload()
                    ->relationship('roles','name')
                    ->visible(fn () => auth()->user()->can('assign roles')),

                Forms\Components\Select::make('permissions')
                    ->label('Permissions (รายคน)')
                    ->multiple()
                    ->preload()
                    ->relationship('permissions','name')
                    ->visible(fn () => auth()->user()->can('assign permissions')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อ')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('บทบาท')->separator(', ')->colors(['primary'])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('กรองตามบทบาท')
                    ->relationship('roles','name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('edit users')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) =>
                        auth()->user()->can('delete users') && $record->id !== auth()->id()
                    ),

                Tables\Actions\Action::make('resetPassword')
                    ->label('รีเซ็ตรหัสผ่าน')
                    ->icon('heroicon-o-key')
                    ->visible(fn () => auth()->user()->can('reset user passwords'))
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('รหัสผ่านใหม่')->password()->revealable()
                            ->required()->rule(Password::defaults()),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('ยืนยันรหัสผ่านใหม่')->password()->revealable()
                            ->required()->same('new_password'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['new_password'])]);
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

**หมายเหตุ**
- User ต้อง use HasRoles; แล้ว (ทำไว้ใน Step 5)

# Step 6 — Dashboard & Report (Filament 3)

---

## 6.1 สร้าง Widgets สำหรับ Dashboard

ใช้ Artisan Command เพื่อสร้าง Widgets ที่จะแสดงบน Dashboard:

```bash
php artisan make:filament-widget BorrowStatsOverview
php artisan make:filament-widget BorrowTrendChart
php artisan make:filament-widget LowStockEquipment --table
```

- BorrowStatsOverview → ใช้สำหรับแสดงสถิติการยืม (จำนวนการยืมทั้งหมด, ยืมวันนี้, คืนแล้ว ฯลฯ)
- BorrowTrendChart → ใช้สำหรับแสดงกราฟแนวโน้มการยืม/คืนอุปกรณ์ตามช่วงเวลา
- LowStockEquipment → ใช้สำหรับแสดงตารางรายการอุปกรณ์ที่ใกล้หมดสต็อก

### 6.1.1 สรุปสถิติ (การ์ด)

ไฟล์: `app/Filament/Widgets/BorrowStatsOverview.php`

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Borrow;
use App\Models\Equipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class BorrowStatsOverview extends BaseWidget
{
    protected ?string $heading = 'สรุปภาพรวม';
    protected static ?string $pollingInterval = '30s'; // auto refresh ทุก 30 วินาที

    public static function canView(): bool
    {
        return auth()->user()?->can('view admin') ?? false;
    }

    protected function getCards(): array
    {
        $active   = Borrow::whereNull('returned_at')->count(); // ยืมค้าง
        $overdue  = Borrow::whereNull('returned_at')
                          ->whereDate('due_at','<',now()->toDateString())
                          ->count(); // เกินกำหนด
        $lowStock = Equipment::where('stock','<=',2)->count(); // สต็อกต่ำ

        return [
            Card::make('ยืมค้าง', $active)
                ->description('ยังไม่คืน')
                ->color($active > 0 ? 'warning' : 'success'),

            Card::make('เกินกำหนด', $overdue)
                ->description('ต้องติดตาม')
                ->color($overdue > 0 ? 'danger' : 'success'),

            Card::make('สต็อกต่ำ (≤2)', $lowStock)
                ->description('รายการ')
                ->color($lowStock > 0 ? 'warning' : 'success'),
        ];
    }
}
```

**📌 คำอธิบาย:**

- `StatsOverviewWidget` ใช้ทำการ์ดแสดงตัวเลขสรุป
- เพิ่ม auto refresh ทุก 30 วินาที ด้วย `$pollingInterval`
- ใช้ policy check (canView) ให้แสดงเฉพาะผู้ที่มีสิทธิ์ view admin
- การ์ดแต่ละใบจะเปลี่ยน สี ตามเงื่อนไข:
    - warning = มีค้าง/สต็อกต่ำ
    - danger = มีเกินกำหนด
    - success = ปกติ

### 6.1.2 กราฟแนวโน้มการยืม (14 วัน)

ไฟล์: `app/Filament/Admin/Widgets/BorrowTrendChart.php`

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Borrow;
use Filament\Widgets\ChartWidget;

class BorrowTrendChart extends ChartWidget
{
    protected ?string $heading = 'แนวโน้มการยืม (14 วันล่าสุด)';

    public static function canView(): bool
    {
        return auth()->user()?->can('view admin') ?? false;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $from = now()->subDays(13)->startOfDay();
        $to   = now()->endOfDay();

        $rows = Borrow::query()
            ->selectRaw('DATE(borrowed_at) as d, COUNT(*) as c')
            ->whereBetween('borrowed_at', [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c','d');

        $labels = [];
        $data = [];
        for ($i=0; $i<14; $i++) {
            $date = now()->subDays(13 - $i)->toDateString();
            $labels[] = date('d/m', strtotime($date));
            $data[] = (int)($rows[$date] ?? 0);
        }

        return [
            'datasets' => [[ 'label' => 'จำนวนการยืม', 'data' => $data ]],
            'labels' => $labels,
        ];
    }
}
```

### 6.1.3 ตาราง “สต็อกต่ำ”

ไฟล์: `app/Filament/Admin/Widgets/LowStockEquipment.php`

```php
<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Equipment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockEquipment extends BaseWidget
{
    protected static ?string $heading = 'อุปกรณ์สต็อกต่ำ (≤2)';

    public static function canView(): bool
    {
        return auth()->user()?->can('view admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Equipment::query()
                    ->where('stock','<=',2)
                    ->orderBy('stock')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('รหัส')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('หมวด'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('คงเหลือ'),
            ]);
    }
}
```

**📌 คำอธิบาย**

- Widget นี้ใช้ `TableWidget` เพื่อแสดงตารางอุปกรณ์ที่ สต็อกเหลือน้อยกว่าหรือเท่ากับ 2
- มี column:
    - รหัส (`code`) → ค้นหาได้
    - ชื่อ (`name`) → ค้นหาได้
    - หมวด (`category.name`) → ใช้ relation แสดงชื่อหมวด
    - คงเหลือ (`stock`) → จำนวนที่เหลือในสต็อก
- แสดงเฉพาะผู้ที่มีสิทธิ์ view admin

## 6.2 สร้างหน้า Report (ฟิลเตอร์ + Export CSV)

รันคำสั่งสร้างหน้าเพจ:
```bash
php artisan make:filament-page BorrowReport
```

### 6.2.1 โค้ด Page (HasTable + Export)

ไฟล์: `app/Filament/Admin/Pages/BorrowReport.php`

```php
<?php

namespace App\Filament\Admin\Pages;

use App\Models\Borrow;
use App\Models\Equipment;
use App\Models\User;
use Filament\Forms\Components\{DatePicker, Section, Select};
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BorrowReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Borrow Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $slug = 'borrow-report';
    protected static string $view = 'filament.admin.pages.borrow-report';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view reports');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view reports'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Borrow::query()->with(['user','equipment']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('ผู้ยืม')->searchable(),
                Tables\Columns\TextColumn::make('equipment.code')->label('รหัส'),
                Tables\Columns\TextColumn::make('equipment.name')->label('อุปกรณ์')->searchable(),
                Tables\Columns\TextColumn::make('borrowed_at')->label('ยืมเมื่อ')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('due_at')->label('กำหนดคืน')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('returned_at')->label('คืนเมื่อ')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('สถานะ')
                    ->colors(['warning'=>'borrowed','success'=>'returned','danger'=>'overdue'])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('period')
                    ->label('ช่วงวันที่ยืม')
                    ->form([
                        DatePicker::make('from')->label('จาก'),
                        DatePicker::make('to')->label('ถึง'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['from'] ?? null) $query->whereDate('borrowed_at','>=',$data['from']);
                        if ($data['to'] ?? null)   $query->whereDate('borrowed_at','<=',$data['to']);
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('ผู้ยืม')
                    ->options(fn()=> User::orderBy('name')->pluck('name','id')->all()),

                Tables\Filters\SelectFilter::make('equipment_id')
                    ->label('อุปกรณ์')
                    ->options(fn()=> Equipment::orderBy('name')->pluck('name','id')->all()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'borrowed'=>'ยืมอยู่',
                        'returned'=>'คืนแล้ว',
                        'overdue' =>'เกินกำหนด',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->visible(fn() => auth()->user()->can('export borrows'))
                    ->action(fn() => $this->streamCsv()),
            ])
            ->defaultSort('borrowed_at','desc')
            ->emptyStateHeading('ยังไม่มีข้อมูล');
    }

    protected function streamCsv(): StreamedResponse
    {
        abort_unless(auth()->user()->can('export borrows'), 403);

        $filename = 'borrow-report-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // หัวตาราง
            fputcsv($out, ['#','ผู้ยืม','รหัส','อุปกรณ์','ยืมเมื่อ','กำหนดคืน','คืนเมื่อ','สถานะ']);

            // ดึง query หลังผ่านฟิลเตอร์ในตารางแล้ว
            $query = $this->getFilteredTableQuery()->orderBy('id');

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        optional($r->user)->name,
                        optional($r->equipment)->code,
                        optional($r->equipment)->name,
                        optional($r->borrowed_at)?->format('Y-m-d H:i'),
                        optional($r->due_at)?->format('Y-m-d'),
                        optional($r->returned_at)?->format('Y-m-d H:i'),
                        $r->status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
```

### 6.2.2 สร้าง View ของเพจรายงาน

**ไฟล์:** `resources/views/filament/pages/borrow-report.blade.php`

```blade
<x-filament::page>
    {{ $this->table }}
</x-filament::page>
```

# Step 7 — ระบบอัตโนมัติ Overdue + Deploy/QA

---

## 7.1 คำสั่งอัปเดตสถานะ Overdue

สร้าง Artisan Command:
```bash
php artisan make:command MarkOverdueBorrows
```

Laravel จะสร้างไฟล์ที่ `app/Console/Commands/MarkOverdueBorrows.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkOverdueBorrows extends Command
{
    protected $signature = 'borrows:mark-overdue {--dry-run : แสดงจำนวนที่จะถูกอัปเดตโดยไม่เขียนจริง}';
    protected $description = 'อัปเดตสถานะรายการยืมที่เกินกำหนด (returned_at IS NULL และ due_at < วันนี้) ให้เป็น overdue';

    public function handle(): int
    {
        // ใช้โซนเวลาแอป (ควรกำหนด Asia/Bangkok ใน .env/config)
        $today = now()->toDateString();

        // นับก่อน
        $count = DB::table('borrows')
            ->whereNull('returned_at')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', $today)
            ->where('status', '!=', 'overdue')
            ->count();

        if ($this->option('dry-run')) {
            $this->info("พบรายการที่จะถูกอัปเดตเป็น overdue จำนวน: {$count}");
            return self::SUCCESS;
        }

        // อัปเดตแบบ bulk (เร็วและปลอดภัย)
        $updated = DB::table('borrows')
            ->whereNull('returned_at')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', $today)
            ->where('status', '!=', 'overdue')
            ->update([
                'status'     => 'overdue',
                'updated_at' => now(),
            ]);

        $this->info("อัปเดตสถานะ overdue แล้ว {$updated}/{$count} รายการ");
        return self::SUCCESS;
    }
}
```

**การทดสอบคำสั่ง MarkOverdueBorrows**

```bash
php artisan borrows:mark-overdue --dry-run
php artisan borrows:mark-overdue
```

**`--dry-run`** เป็น **option เสริม** ที่เราสามารถกำหนดให้กับ Artisan Command (หรือโปรแกรมอื่น ๆ)  เพื่อให้คำสั่ง **“ลองรัน”** (simulate) โดย **ไม่กระทบข้อมูลจริง**  

> เคล็ดลับ: หากต้องการแจ้งเตือน (อีเมล/ข้อความ) เพิ่มเติม ให้ต่อยอดหลังอัปเดตสำเร็จ (เช่น select IDs แล้ว dispatch Job ส่งอีเมล)

## 7.2 ตั้ง Scheduler เรียกคำสั่งอัตโนมัติ

แก้ไฟล์ **`app/Console/Kernel.php`**:

```php
protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
{
    // รันทุกวัน เวลา 00:15 ตาม timezone ของแอป
    $schedule->command('borrows:mark-overdue')->dailyAt('00:15');

    // (ออปชัน) เคลียร์แคชรายวันเบา ๆ
    // $schedule->command('optimize')->dailyAt('03:30');
}
```

> Laravel Scheduler จะใช้ timezone จาก `config/app.php` (`APP_TIMEZONE=Asia/Bangkok`)

## 7.3 ตั้ง Cron บนเซิร์ฟเวอร์

บนเครื่อง production ให้เพิ่ม cron ที่ผู้ใช้เว็บเซิร์ฟเวอร์ (เช่น www-data) เรียก Scheduler ทุกนาที:

```cron
* * * * * php /path/to/current/artisan schedule:run >> /dev/null 2>&1
```

เช็คทำงาน:
```bash
php artisan schedule:run
# ดู log ของระบบหรือเพิ่ม $this->info ใน command เพื่อตรวจสอบ
```

## 7.4 Deploy

1. สร้าง Symlink จัดเก็บไฟล์:

```bash
php artisan storage:link
```

2. สิทธิ์โฟลเดอร์:

```bash
chmod -R 775 storage bootstrap/cache
```

3. ปรับ .env สำหรับ Production:
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_DOMAIN
APP_TIMEZONE=Asia/Bangkok
LOG_LEVEL=info
```

4. Optimize & Cache:
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```