<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('M j, Y g:i a')->description(fn (ActivityLog $record): string => $record->created_at->diffForHumans())->sortable(),
                TextColumn::make('user.name')->label('Who')->placeholder('System')->searchable(),
                TextColumn::make('event')->badge()->color(fn (string $state): string => match ($state) {
                    'created' => 'success',
                    'deleted' => 'danger',
                    'paid' => 'success',
                    default => 'gray',
                }),
                TextColumn::make('description')->wrap()->searchable(),
            ])
            ->filters([
                SelectFilter::make('event')->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'paid' => 'Paid', 'resent' => 'Resent', 'refunded' => 'Refunded']),
                SelectFilter::make('user')->relationship('user', 'name'),
            ])
            ->emptyStateHeading('No activity recorded yet');
    }
}
