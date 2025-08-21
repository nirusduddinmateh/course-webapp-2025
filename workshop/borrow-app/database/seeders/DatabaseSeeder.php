<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /// 1) สร้างหมวด 5 อันก่อน (พอสำหรับอุปกรณ์หลายชิ้น)
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
}
