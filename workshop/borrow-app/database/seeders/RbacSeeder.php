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
