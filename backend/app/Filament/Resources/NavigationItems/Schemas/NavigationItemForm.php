<?php

namespace App\Filament\Resources\NavigationItems\Schemas;

use App\Enums\NavigationLocation;
use App\Models\NavigationItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class NavigationItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('location')
                    ->options(NavigationLocation::class)
                    ->required()
                    ->live()
                    ->default(NavigationLocation::Header),
                Select::make('parent_id')
                    ->label('Parent item')
                    ->placeholder('Top level')
                    ->options(fn (Get $get, ?NavigationItem $record): array => NavigationItem::query()
                        ->whereNull('parent_id')
                        ->where('location', $get('location'))
                        ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                        ->orderBy('sort_order')
                        ->pluck('label', 'id')
                        ->all())
                    ->helperText('Child items appear in the main menu’s dropdown panel.'),
                TextInput::make('label')->required()->maxLength(80),
                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('/services')
                    ->regex('/^(\/|https?:\/\/|mailto:|tel:|#)/')
                    ->helperText('Internal paths start with “/”.'),
                TextInput::make('description')
                    ->maxLength(190)
                    ->columnSpanFull()
                    ->helperText('Optional supporting line shown in the desktop mega menu.'),
                TextInput::make('sort_order')->label('Display order')->numeric()->minValue(0)->default(0),
                Grid::make(2)->schema([
                    Toggle::make('is_visible')->label('Visible')->default(true),
                    Toggle::make('open_in_new_tab')->label('Open in new tab'),
                ]),
            ]),
        ]);
    }
}
