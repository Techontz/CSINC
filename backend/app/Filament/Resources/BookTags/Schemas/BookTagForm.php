<?php

namespace App\Filament\Resources\BookTags\Schemas;

use App\Models\BookTag;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BookTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(60)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')
                ->required()
                ->alphaDash()
                ->maxLength(80)
                ->unique(BookTag::class, 'slug', ignoreRecord: true),
        ]);
    }
}
