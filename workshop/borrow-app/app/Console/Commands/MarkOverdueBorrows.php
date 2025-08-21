<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MarkOverdueBorrows extends Command
{
    protected $signature = 'borrows:mark-overdue {--dry-run : แสดงจำนวนที่จะถูกอัปเดตโดยไม่จริง}';
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
