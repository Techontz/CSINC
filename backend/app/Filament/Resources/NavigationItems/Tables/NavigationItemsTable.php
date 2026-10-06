<?php

namespace App\Filament\Resources\NavigationItems\Tables;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NavigationItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('parent')
                ->orderByRaw('COALESCE((select p.sort_order from navigation_items p where p.id = navigation_items.parent_id), navigation_items.sort_order)')
                ->orderByRaw('parent_id is not null')
                ->orderBy('sort_order'))
            ->reorderable('sort_order')
            ->defaultGroup(Group::make('location')->getTitleFromRecordUsing(fn (NavigationItem $record): string => $record->location->getLabel())->collapsible())
            ->paginated(false)
            ->columns([
                TextColumn::make('label')
                    ->weight(fn (NavigationItem $record): FontWeight => $record->parent_id ? FontWeight::Normal : FontWeight::SemiBold)
                    ->formatStateUsing(fn (NavigationItem $record, string $state): string => $record->parent_id ? '↳  '.$state : $state)
                    ->description(fn (NavigationItem $record): ?string => $record->description)
                    ->searchable(),
                TextColumn::make('url')->label('URL')->color('gray')->fontFamily('mono')->size('sm'),
                ToggleColumn::make('is_visible')->label('Visible'),
            ])
            ->filters([SelectFilter::make('location')->options(NavigationLocation::class)])
            ->recordActions([EditAction::make()->iconButton(), DeleteAction::make()->iconButton()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
