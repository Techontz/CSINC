<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageStatus;
use App\Filament\Forms\SeoSection;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('Page')
                        ->icon('heroicon-o-document')
                        ->columns(2)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(190)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                    if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug((string) $old))) {
                                        $set('slug', Str::slug((string) $state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->required()
                                ->alphaDash()
                                ->maxLength(190)
                                ->prefix('/')
                                ->unique(Page::class, 'slug', ignoreRecord: true)
                                ->notIn(PageResource::RESERVED_SLUGS)
                                ->disabled(fn (?Page $record): bool => (bool) $record?->is_system)
                                ->helperText(fn (?Page $record): ?string => $record?->is_system ? 'System page — its address is fixed.' : null),
                            Textarea::make('summary')
                                ->rows(2)
                                ->maxLength(500)
                                ->columnSpanFull()
                                ->helperText('Short introduction; also used as the default meta description.'),
                        ]),
                    Section::make('Content')
                        ->icon('heroicon-o-squares-plus')
                        ->description('Build the page from sections. Drag to reorder; changes go live when saved.')
                        ->schema([PageBlocks::make()]),
                    SeoSection::make(fn (Get $get): string => PageResource::publicPath((string) $get('slug')), 'title', 'summary'),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Publishing')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Select::make('status')
                                ->options(PageStatus::class)
                                ->default(PageStatus::Draft)
                                ->selectablePlaceholder(false)
                                ->required(),
                            TextEntry::make('updated_at')
                                ->label('Last updated')
                                ->state(fn (?Page $record): ?string => $record?->updated_at?->format('M j, Y g:i a').($record?->editor ? ' by '.$record->editor->name : ''))
                                ->visible(fn (?Page $record): bool => (bool) $record),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }
}
