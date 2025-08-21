<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EquipmentResource\Pages;
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
}
