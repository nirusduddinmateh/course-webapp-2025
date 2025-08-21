<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Borrow;
use Filament\Widgets\ChartWidget;

class BorrowTrendChart extends ChartWidget
{
    protected static ?string $heading = 'แนวโน้มการยืม (14 วันล่าสุด)';

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
