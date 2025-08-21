### ทำตามขั้นตอน

#### 1) สร้าง Model + Migration
```bash
php artisan make:model Customer -m
```
**ไฟล์:** `database/migrations/xxxx_xx_xx_create_customers_table.php`

```php
public function up(): void
{
    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name', 120);
        $table->string('email')->unique();
        $table->string('phone', 30)->nullable();
        $table->timestamps();
    });
}
```

Run:
```bash
php artisan migrate
```

#### 2) สร้าง Filament Resource
```bash
php artisan make:filament-resource Customer --generate
```
จะได้ไฟล์หลัก:
- `app/Filament/Resources/CustomerResource.php`
- `app/Filament/Resources/CustomerResource/Pages/*`

#### 3) ปรับแต่งฟอร์มและตาราง

**ไฟล์:** `app/Filament/Resources/CustomerResource.php`

```php
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('name')
            ->label('ชื่อ')
            ->required()
            ->maxLength(120),

        TextInput::make('email')
            ->label('อีเมล')
            ->email()
            ->required()
            ->unique(ignoreRecord: true),

        TextInput::make('phone')
            ->label('เบอร์โทร')
            ->maxLength(30),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')->label('ชื่อลูกค้า')->searchable()->sortable(),
            TextColumn::make('email')->label('อีเมล')->searchable()->sortable(),
        ])
        ->filters([
            Filter::make('has_phone')
                ->label('เฉพาะลูกค้าที่มีเบอร์โทร')
                ->query(fn ($q) => $q->whereNotNull('phone')->where('phone', '<>', '')),
        ])
        ->defaultSort('name');
}
```

> ถ้าคุณใช้ namespace แบบ `Forms\Components\TextInput` / `Tables\Columns\TextColumn` ให้แก้ให้ตรงกับ use ของไฟล์ที่เครื่องคุณสร้างมา

#### 4) ทดลองใช้งาน

- เปิด `/admin/customers`
- สร้างลูกค้าใหม่ (กรอก `name`, `email`, `phone`)
- แก้ไข/ลบ รายการได้จากหน้าตาราง
- ลองกด Filter → “เฉพาะลูกค้าที่มีเบอร์โทร” → ตารางควรแสดงเฉพาะรายการที่ `phone` ไม่ว่าง

> **เคล็ดลับ:** ถ้าต้องการให้เบอร์โทรเป็น Unique ด้วย ให้เพิ่ม `->unique(ignoreRecord: true)` ในฟิลด์ `phone` และเพิ่ม unique index ใน migration ภายหลัง