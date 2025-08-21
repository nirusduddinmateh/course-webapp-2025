<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EquipmentCategoryResource\Pages;
use App\Models\EquipmentCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentCategoryResource extends Resource
{
    protected static ?string $model = EquipmentCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Categories';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('ชื่อหมวด')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อหมวด')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('equipment_count')
                    ->counts('equipment')
                    ->label('จำนวนอุปกรณ์')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('อัปเดต')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEquipmentCategories::route('/'),
            'create' => Pages\CreateEquipmentCategory::route('/create'),
            'edit'   => Pages\EditEquipmentCategory::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view equipment_categories');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view equipment_categories');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create equipment_categories');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit equipment_categories');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete equipment_categories');
    }
}
