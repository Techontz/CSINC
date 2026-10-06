<?php

namespace App\Filament\Resources\BookCategories\Tables;

use App\Models\BookCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BookCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('books'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->description(fn (BookCategory $record): ?string => $record->headline),
                TextColumn::make('slug')->color('gray')->prefix('/product-category/'),
                TextColumn::make('books_count')->label('Products')->badge()->color('gray')->sortable(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable(),
            ])
            ->filters([TernaryFilter::make('is_published')->label('Published')])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalDescription('Products in this category will remain, but will no longer be listed under it.'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Categories group products on the website, e.g. Healthcare and General Business.');
    }
}
