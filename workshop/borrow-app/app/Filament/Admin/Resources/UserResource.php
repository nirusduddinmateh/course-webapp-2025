<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Tables\Filters\SelectFilter;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Admin';
    protected static ?string $navigationLabel = 'Users';

    /* -------- RBAC: ซ่อนเมนูถ้าไม่มีสิทธิ์ -------- */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view users');
    }

    /* -------- RBAC: คุม CRUD -------- */
    public static function canViewAny(): bool      { return auth()->user()->can('view users'); }
    public static function canCreate(): bool       { return auth()->user()->can('create users'); }
    public static function canEdit($record): bool  { return auth()->user()->can('edit users'); }
    public static function canDelete($record): bool
    {
        if ($record && $record->id === auth()->id()) return false; // ห้ามลบตัวเอง
        return auth()->user()->can('delete users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ข้อมูลผู้ใช้')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('ชื่อ')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('email')
                    ->label('อีเมล')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
            ])->columns(2),

            Forms\Components\Section::make('รหัสผ่าน')->schema([
                Forms\Components\TextInput::make('password')
                    ->label('รหัสผ่าน')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->rule(Password::defaults()),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label('ยืนยันรหัสผ่าน')
                    ->password()
                    ->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->same('password'),
            ])->columns(2)
                ->visible(fn () => auth()->user()->can('reset user passwords')
                    || request()->routeIs('filament.resources.users.create')),

            Forms\Components\Section::make('บทบาท/สิทธิ์')->schema([
                Forms\Components\Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->preload()
                    ->relationship('roles','name')
                    ->visible(fn () => auth()->user()->can('assign roles')),

                Forms\Components\Select::make('permissions')
                    ->label('Permissions (รายคน)')
                    ->multiple()
                    ->preload()
                    ->relationship('permissions','name')
                    ->visible(fn () => auth()->user()->can('assign permissions')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ชื่อ')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('อีเมล')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('บทบาท')->separator(', ')->colors(['primary'])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('สร้างเมื่อ')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('กรองตามบทบาท')
                    ->relationship('roles','name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('edit users')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) =>
                        auth()->user()->can('delete users') && $record->id !== auth()->id()
                    ),

                Tables\Actions\Action::make('resetPassword')
                    ->label('รีเซ็ตรหัสผ่าน')
                    ->icon('heroicon-o-key')
                    ->visible(fn () => auth()->user()->can('reset user passwords'))
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('รหัสผ่านใหม่')->password()->revealable()
                            ->required()->rule(Password::defaults()),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('ยืนยันรหัสผ่านใหม่')->password()->revealable()
                            ->required()->same('new_password'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->update(['password' => Hash::make($data['new_password'])]);
                    })
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
