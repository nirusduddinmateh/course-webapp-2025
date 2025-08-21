<?php

namespace App\Filament\Admin\Pages;

use App\Models\Borrow;
use App\Models\Equipment;
use App\Models\User;
use Filament\Forms\Components\{DatePicker, Section, Select};
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BorrowReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Borrow Report';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $slug = 'borrow-report';
    protected static string $view = 'filament.admin.pages.borrow-report';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('view reports');
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('view reports'), 403);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Borrow::query()->with(['user','equipment']))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('ผู้ยืม')->searchable(),
                Tables\Columns\TextColumn::make('equipment.code')->label('รหัส'),
                Tables\Columns\TextColumn::make('equipment.name')->label('อุปกรณ์')->searchable(),
                Tables\Columns\TextColumn::make('borrowed_at')->label('ยืมเมื่อ')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('due_at')->label('กำหนดคืน')->dateTime('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('returned_at')->label('คืนเมื่อ')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('สถานะ')
                    ->colors(['warning'=>'borrowed','success'=>'returned','danger'=>'overdue'])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('period')
                    ->label('ช่วงวันที่ยืม')
                    ->form([
                        DatePicker::make('from')->label('จาก'),
                        DatePicker::make('to')->label('ถึง'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['from'] ?? null) $query->whereDate('borrowed_at','>=',$data['from']);
                        if ($data['to'] ?? null)   $query->whereDate('borrowed_at','<=',$data['to']);
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('ผู้ยืม')
                    ->options(fn()=> User::orderBy('name')->pluck('name','id')->all()),

                Tables\Filters\SelectFilter::make('equipment_id')
                    ->label('อุปกรณ์')
                    ->options(fn()=> Equipment::orderBy('name')->pluck('name','id')->all()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'borrowed'=>'ยืมอยู่',
                        'returned'=>'คืนแล้ว',
                        'overdue' =>'เกินกำหนด',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->visible(fn() => auth()->user()->can('export borrows'))
                    ->action(fn() => $this->streamCsv()),
            ])
            ->defaultSort('borrowed_at','desc')
            ->emptyStateHeading('ยังไม่มีข้อมูล');
    }

    protected function streamCsv(): StreamedResponse
    {
        abort_unless(auth()->user()->can('export borrows'), 403);

        $filename = 'borrow-report-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // หัวตาราง
            fputcsv($out, ['#','ผู้ยืม','รหัส','อุปกรณ์','ยืมเมื่อ','กำหนดคืน','คืนเมื่อ','สถานะ']);

            // ดึง query หลังผ่านฟิลเตอร์ในตารางแล้ว
            $query = $this->getFilteredTableQuery()->orderBy('id');

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        optional($r->user)->name,
                        optional($r->equipment)->code,
                        optional($r->equipment)->name,
                        optional($r->borrowed_at)?->format('Y-m-d H:i'),
                        optional($r->due_at)?->format('Y-m-d'),
                        optional($r->returned_at)?->format('Y-m-d H:i'),
                        $r->status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
