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
