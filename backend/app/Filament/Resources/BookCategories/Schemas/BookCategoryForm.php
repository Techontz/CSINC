<?php

namespace App\Filament\Resources\BookCategories\Schemas;

use App\Filament\Forms\MediaPicker;
use App\Filament\Forms\SeoSection;
use App\Models\BookCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BookCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Category')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(120)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                    if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug((string) $old))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->required()
                                ->alphaDash()
                                ->maxLength(120)
                                ->prefix('/product-category/')
                                ->unique(BookCategory::class, 'slug', ignoreRecord: true),
                            TextInput::make('headline')
                                ->maxLength(190)
                                ->helperText('Heading shown on the category page, e.g. “Healthcare & Residential Care Startup Guides”.'),
                            Textarea::make('description')->rows(3)->maxLength(2000),
                        ]),
                    SeoSection::make(fn (Get $get): string => '/product-category/'.($get('slug') ?: 'slug'), 'name', 'description', null),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Visibility')
                        ->schema([
                            Toggle::make('is_published')->label('Published')->default(true),
                            TextInput::make('sort_order')->label('Display order')->numeric()->minValue(0)->default(0),
                        ]),
                    Section::make('Image')
                        ->schema([MediaPicker::make('image_id', 'categories')->hiddenLabel()]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
