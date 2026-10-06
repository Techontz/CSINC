<?php

namespace App\Filament\Resources\Services\Tables;

use App\Enums\ServiceGroup;
use App\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('image'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->defaultGroup(Group::make('group')->getTitleFromRecordUsing(fn (Service $record): string => $record->group->getLabel()))
            ->columns([
                ImageColumn::make('image.path')->label('')->disk('public')->imageWidth(64)->imageHeight(44)->extraImgAttributes(['class' => 'rounded object-cover']),
                TextColumn::make('title')
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (Service $record): string => str($record->summary)->limit(90)->toString())
                    ->wrap(),
                TextColumn::make('highlights')->badge()->color('gray')->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->filters([SelectFilter::make('group')->options(ServiceGroup::class)])
            ->recordActions([EditAction::make()->iconButton(), DeleteAction::make()->iconButton()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
