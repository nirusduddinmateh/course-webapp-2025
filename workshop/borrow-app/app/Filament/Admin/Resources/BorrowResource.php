<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BorrowResource\Pages;
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
                    })->visible(fn ($record) => $record->returned_at === null && auth()->user()->can('return borrows')),
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
}
