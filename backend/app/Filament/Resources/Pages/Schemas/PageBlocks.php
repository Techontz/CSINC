<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\ServiceGroup;
use App\Filament\Forms\MediaPicker;
use App\Models\BookCategory;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Block catalogue for page building. Each block maps 1:1 to a section
 * component in the Next.js frontend.
 */
class PageBlocks
{
    private const ACCENT_HELP = 'Wrap words in *asterisks* to set them in the italic accent style.';

    private const TOKEN_HELP = 'Live figures: {{products.count}}, {{category.healthcare.count}}, {{category.general-business.count}}.';

    public static function make(): Builder
    {
        return Builder::make('blocks')
            ->label('Page sections')
            ->blocks([
                self::heroSlider(),
                self::hero(),
                self::statement(),
                self::pillars(),
                self::serviceLinks(),
                self::results(),
                self::services(),
                self::books(),
                self::bookCategories(),
                self::split(),
                self::stats(),
                self::values(),
                self::faq(),
                self::ctaBand(),
                self::richText(),
                self::contact(),
            ])
            ->blockNumbers(false)
            ->collapsible()
            ->collapsed()
            ->cloneable()
            ->reorderableWithButtons()
            ->addActionLabel('Add section')
            ->blockPickerColumns(3)
            ->blockPickerWidth('3xl')
            ->columnSpanFull();
    }

    private static function labelled(string $label): \Closure
    {
        return fn (?array $state): string => $label.(filled($state['heading'] ?? $state['text'] ?? null)
            ? ' — '.str(strip_tags(str_replace('*', '', (string) ($state['heading'] ?? $state['text']))))->limit(60)
            : '');
    }

    private static function heading(bool $required = true): TextInput
    {
        return TextInput::make('heading')->required($required)->maxLength(190)->helperText(self::ACCENT_HELP);
    }

    private static function eyebrow(): TextInput
    {
        return TextInput::make('eyebrow')->label('Eyebrow')->maxLength(120)->helperText('Small label above the heading.');
    }

    /**
     * @return list<TextInput>
     */
    private static function link(string $prefix = 'link', string $label = 'Link'): array
    {
        return [
            TextInput::make($prefix.'_label')->label($label.' label')->maxLength(60),
            TextInput::make($prefix.'_url')->label($label.' URL')->maxLength(255)->placeholder('/contact-us')
                ->requiredWith($prefix.'_label')
                ->regex('/^(\/|https?:\/\/|mailto:|tel:|#)/'),
        ];
    }

    private static function hero(): Block
    {
        return Block::make('hero')
            ->label(self::labelled('Hero'))
            ->icon('heroicon-o-sparkles')
            ->schema([
                Select::make('variant')
                    ->options(['split' => 'Text with image', 'full' => 'Full-bleed image', 'text' => 'Text only'])
                    ->default('split')
                    ->selectablePlaceholder(false)
                    ->required(),
                self::eyebrow(),
                self::heading(),
                Textarea::make('body')->rows(3)->maxLength(600),
                Grid::make(2)->schema([...self::link('primary', 'Primary button'), ...self::link('secondary', 'Secondary button')]),
                MediaPicker::make('image_id', 'site')->label('Image')->visible(fn (Get $get): bool => $get('variant') !== 'text'),
            ]);
    }

    private static function heroSlider(): Block
    {
        return Block::make('hero_slider')
            ->label(fn (?array $state): string => 'Video hero slider'.(filled($state['slides'] ?? null) ? ' — '.count($state['slides']).' slides' : ''))
            ->icon('heroicon-o-film')
            ->schema([
                Repeater::make('slides')
                    ->schema([
                        TextInput::make('eyebrow')->label('Label')->maxLength(60)->helperText('Short label above the heading, e.g. “Business services”.'),
                        TextInput::make('heading')->required()->maxLength(190)->helperText(self::ACCENT_HELP),
                        Textarea::make('body')->rows(2)->maxLength(300)->helperText(self::TOKEN_HELP),
                        Grid::make(2)->schema(self::link()),
                        Grid::make(2)->schema([
                            MediaPicker::make('video_id', 'hero', 'video')->label('Background video'),
                            MediaPicker::make('image_id', 'hero')->label('Poster / fallback image')
                                ->helperText('Shown while the video loads and for visitors who prefer reduced motion.'),
                        ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => isset($state['heading']) ? str_replace('*', '', $state['heading']) : null)
                    ->collapsible()
                    ->reorderableWithButtons()
                    ->minItems(1)
                    ->maxItems(6)
                    ->defaultItems(1),
                TextInput::make('interval')
                    ->label('Seconds per slide')
                    ->numeric()
                    ->minValue(4)
                    ->maxValue(20)
                    ->default(7),
            ]);
    }

    private static function serviceLinks(): Block
    {
        return Block::make('service_links')
            ->label(self::labelled('Navy band with service links'))
            ->icon('heroicon-o-queue-list')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Textarea::make('body')->rows(3)->maxLength(500),
                MediaPicker::make('image_id', 'site')->label('Image')->required(),
                Select::make('group')->label('Services to list')->options(ServiceGroup::class)->placeholder('All services'),
            ]);
    }

    private static function results(): Block
    {
        return Block::make('results')
            ->label(self::labelled('Highlight carousel'))
            ->icon('heroicon-o-presentation-chart-bar')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Grid::make(2)->schema(self::link()),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('eyebrow')->label('Label')->maxLength(80),
                        TextInput::make('value')->required()->maxLength(20)->helperText(self::TOKEN_HELP),
                        TextInput::make('label')->label('Description')->required()->maxLength(160),
                        TextInput::make('url')->label('Link')->maxLength(255)->regex('/^(\/|https?:\/\/)/'),
                        MediaPicker::make('image_id', 'site')->label('Background image'),
                    ])
                    ->minItems(1)
                    ->maxItems(6)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
            ]);
    }

    private static function statement(): Block
    {
        return Block::make('statement')
            ->label(self::labelled('Editorial statement'))
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->schema([
                self::eyebrow(),
                Textarea::make('text')->required()->rows(3)->maxLength(600)->helperText(self::ACCENT_HELP),
                Grid::make(2)->schema(self::link()),
            ]);
    }

    private static function pillars(): Block
    {
        return Block::make('pillars')
            ->label(self::labelled('Feature cards'))
            ->icon('heroicon-o-view-columns')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Grid::make(2)->schema(self::link('link', '“See all” link')),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        Textarea::make('body')->rows(3)->maxLength(600)->helperText(self::TOKEN_HELP),
                        MediaPicker::make('image_id', 'site')->label('Image'),
                        Grid::make(2)->schema(self::link()),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->collapsible()
                    ->minItems(1)
                    ->maxItems(6)
                    ->defaultItems(3),
            ]);
    }

    private static function services(): Block
    {
        return Block::make('services')
            ->label(self::labelled('Services list'))
            ->icon('heroicon-o-briefcase')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Grid::make(2)->schema([
                    Select::make('group')->label('Service group')->options(ServiceGroup::class)->placeholder('All services'),
                    Select::make('layout')->options(['alternating' => 'Alternating image rows', 'grid' => 'Card grid'])->default('alternating')->selectablePlaceholder(false),
                ]),
            ]);
    }

    private static function books(): Block
    {
        return Block::make('books')
            ->label(self::labelled('Products rail'))
            ->icon('heroicon-o-book-open')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Textarea::make('intro')->rows(2)->maxLength(400)->helperText(self::TOKEN_HELP),
                Grid::make(3)->schema([
                    Select::make('source')
                        ->options(['featured' => 'Featured products', 'latest' => 'Latest products', 'category' => 'From a category'])
                        ->default('featured')
                        ->selectablePlaceholder(false)
                        ->live(),
                    Select::make('category')
                        ->options(fn (): array => BookCategory::query()->ordered()->pluck('name', 'slug')->all())
                        ->visible(fn (Get $get): bool => $get('source') === 'category')
                        ->required(fn (Get $get): bool => $get('source') === 'category'),
                    TextInput::make('limit')->numeric()->minValue(1)->maxValue(24)->default(8),
                ]),
                Select::make('layout')
                    ->options(['carousel' => 'Coloured band with scrolling carousel', 'grid' => 'Grid'])
                    ->default('carousel')
                    ->selectablePlaceholder(false),
                Grid::make(2)->schema(self::link()),
            ]);
    }

    private static function bookCategories(): Block
    {
        return Block::make('book_categories')
            ->label(self::labelled('Product categories'))
            ->icon('heroicon-o-squares-2x2')
            ->schema([self::eyebrow(), self::heading()]);
    }

    private static function split(): Block
    {
        return Block::make('split')
            ->label(self::labelled('Image & text'))
            ->icon('heroicon-o-photo')
            ->schema([
                self::eyebrow(),
                self::heading(),
                RichEditor::make('body_html')
                    ->label('Body')
                    ->toolbarButtons([['bold', 'italic', 'link'], ['bulletList', 'orderedList'], ['undo', 'redo']]),
                MediaPicker::make('image_id', 'site')->label('Image')->required(),
                Grid::make(2)->schema([
                    Select::make('image_position')->options(['left' => 'Image left', 'right' => 'Image right'])->default('right')->selectablePlaceholder(false),
                    Select::make('theme')->options(['light' => 'Light', 'dark' => 'Navy'])->default('light')->selectablePlaceholder(false),
                ]),
                Grid::make(2)->schema(self::link()),
            ]);
    }

    private static function stats(): Block
    {
        return Block::make('stats')
            ->label(self::labelled('Key figures'))
            ->icon('heroicon-o-chart-bar')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('value')->required()->maxLength(40)->helperText(self::TOKEN_HELP),
                        TextInput::make('label')->required()->maxLength(120),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->maxItems(4)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                Grid::make(2)->schema(self::link()),
            ]);
    }

    private static function values(): Block
    {
        return Block::make('values')
            ->label(self::labelled('Mission / values'))
            ->icon('heroicon-o-flag')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('label')->required()->maxLength(80),
                        Textarea::make('body')->required()->rows(3)->maxLength(600),
                    ])
                    ->minItems(1)
                    ->maxItems(6)
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
            ]);
    }

    private static function faq(): Block
    {
        return Block::make('faq')
            ->label(self::labelled('FAQ'))
            ->icon('heroicon-o-question-mark-circle')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('question')->required()->maxLength(255),
                        RichEditor::make('answer')->required()->toolbarButtons([['bold', 'italic', 'link'], ['bulletList']]),
                    ])
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
            ]);
    }

    private static function ctaBand(): Block
    {
        return Block::make('cta_band')
            ->label(self::labelled('Call to action'))
            ->icon('heroicon-o-megaphone')
            ->schema([
                self::eyebrow(),
                self::heading(),
                Textarea::make('body')->rows(2)->maxLength(400),
                Fieldset::make('Buttons')->schema([...self::link('primary', 'Primary'), ...self::link('secondary', 'Secondary')]),
                MediaPicker::make('background_id', 'site')->label('Background image'),
                Select::make('align')->label('Text position')->options(['right' => 'Right (image shows on the left)', 'left' => 'Left'])->default('right')->selectablePlaceholder(false),
            ]);
    }

    private static function richText(): Block
    {
        return Block::make('rich_text')
            ->label('Rich text')
            ->icon('heroicon-o-document-text')
            ->schema([
                RichEditor::make('body_html')
                    ->label('Content')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList', 'blockquote'],
                        ['undo', 'redo'],
                    ]),
            ]);
    }

    private static function contact(): Block
    {
        return Block::make('contact')
            ->label('Contact form & details')
            ->icon('heroicon-o-envelope')
            ->schema([
                TextInput::make('heading')->maxLength(190),
                Textarea::make('intro')->rows(2)->maxLength(400),
            ]);
    }
}
