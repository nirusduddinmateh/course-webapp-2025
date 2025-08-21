<?php

namespace App\Filament\Admin\Resources\BorrowResource\Pages;

use App\Filament\Admin\Resources\BorrowResource;
use App\Models\Borrow;
use App\Models\Equipment;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
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
            Notification::make()
                ->title('ผู้ใช้นี้ยืมอุปกรณ์ชิ้นนี้ค้างอยู่แล้ว')
                ->danger()
                ->send();

            throw new Halt(); // หยุดการสร้างเรคคอร์ด
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
                Notification::make()
                    ->title('สต็อกไม่พอ')
                    ->danger()
                    ->send();

                throw new Halt();
            }

            // 3) หักสต็อก แล้วตั้งสถานะ
            $eq->decrement('stock');
            $data['status'] = 'borrowed';

            return Borrow::create($data);
        });
    }
}
