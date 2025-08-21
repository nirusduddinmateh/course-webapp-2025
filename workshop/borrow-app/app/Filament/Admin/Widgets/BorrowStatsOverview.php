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
