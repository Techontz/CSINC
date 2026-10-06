<?php

namespace App\Filament\Resources\BookTags;

use App\Filament\Resources\BookTags\Pages\ListBookTags;
use App\Filament\Resources\BookTags\Schemas\BookTagForm;
use App\Filament\Resources\BookTags\Tables\BookTagsTable;
use App\Models\BookTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookTagResource extends Resource
{
    protected static ?string $model = BookTag::class;

    protected static ?string $modelLabel = 'tag';

    protected static ?string $slug = 'product-tags';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?string $navigationLabel = 'Tags';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return BookTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookTagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookTags::route('/'),
        ];
    }
}
