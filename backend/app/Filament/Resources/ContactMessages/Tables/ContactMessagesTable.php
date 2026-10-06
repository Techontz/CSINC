<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\MessageStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('full_name')
                    ->label('From')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->weight(fn (ContactMessage $record): FontWeight => $record->status === MessageStatus::New ? FontWeight::Bold : FontWeight::Normal)
                    ->description(fn (ContactMessage $record): string => $record->email),
                TextColumn::make('subject')
                    ->searchable()
                    ->description(fn (ContactMessage $record): string => str($record->message)->limit(80)->toString())
                    ->wrap(),
                TextColumn::make('phone')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M j, Y')
                    ->description(fn (ContactMessage $record): string => $record->created_at->format('g:i a'))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('received')
                    ->schema([DatePicker::make('from')->native(false), DatePicker::make('until')->native(false)])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))),
                TrashedFilter::make(),
            ])
            ->recordActions([ViewAction::make()->iconButton()])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::statusBulk(MessageStatus::Read, 'Mark as read', 'heroicon-m-envelope-open'),
                    self::statusBulk(MessageStatus::Replied, 'Mark as replied', 'heroicon-m-check-circle'),
                    self::statusBulk(MessageStatus::Archived, 'Archive', 'heroicon-m-archive-box'),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading('Nothing here')
            ->emptyStateDescription('Inquiries submitted on the website will appear in this inbox.');
    }

    private static function statusBulk(MessageStatus $status, string $label, string $icon): BulkAction
    {
        return BulkAction::make('bulk_'.$status->value)
            ->label($label)
            ->icon($icon)
            ->authorize(fn (): bool => Auth::user()->can('update', new ContactMessage))
            ->action(fn (Collection $records) => $records->each(fn (ContactMessage $record) => $record->markAs($status, Auth::user())))
            ->deselectRecordsAfterCompletion();
    }
}
