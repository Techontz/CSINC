<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Enums\PageStatus;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('editor'))
            ->defaultSort('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Page $record): string => PageResource::publicPath($record->slug)),
                IconColumn::make('is_system')
                    ->label('System')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-minus')
                    ->tooltip(fn (Page $record): ?string => $record->is_system ? 'Core page — cannot be deleted' : null),
                TextColumn::make('blocks')
                    ->label('Sections')
                    ->state(fn (Page $record): int => count($record->blocks ?? []))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->description(fn (Page $record): ?string => $record->editor?->name),
            ])
            ->filters([
                SelectFilter::make('status')->options(PageStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                ActionGroup::make([
                    Action::make('view')
                        ->label('View on website')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Page $record): string => PageResource::publicUrl($record), shouldOpenInNewTab: true),
                    DeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }
}
