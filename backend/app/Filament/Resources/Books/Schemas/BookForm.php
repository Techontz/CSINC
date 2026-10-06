<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Enums\BookStatus;
use App\Filament\Forms\MediaPicker;
use App\Filament\Forms\SeoSection;
use App\Filament\Support\MoneyInput;
use App\Media\MediaUploader;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookTag;
use App\Models\Media;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    self::contentSection(),
                    self::deliverySection(),
                    self::detailsSection(),
                    self::gallerySection(),
                    SeoSection::make(fn (Get $get): string => '/products/'.($get('slug') ?: 'product-slug')),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    self::publishingSection(),
                    self::coverSection(),
                    self::organizationSection(),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    private static function contentSection(): Section
    {
        return Section::make('Product')
            ->icon('heroicon-o-book-open')
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
                    ->maxLength(190)
                    ->alphaDash()
                    ->unique(Book::class, 'slug', ignoreRecord: true)
                    ->prefix('/products/')
                    ->helperText('Used in the product URL. Changing it on a published product breaks existing links.'),
                TextInput::make('subtitle')->maxLength(190),
                Textarea::make('short_description')
                    ->label('Short description')
                    ->rows(2)
                    ->maxLength(500)
                    ->helperText('One or two sentences shown on product cards and in search results.'),
                RichEditor::make('description')
                    ->label('Full description')
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList', 'blockquote'],
                        ['undo', 'redo'],
                    ])
                    ->maxLength(100_000),
            ]);
    }

    private static function deliverySection(): Section
    {
        return Section::make('Pricing & delivery')
            ->icon('heroicon-o-banknotes')
            ->description('Products with a price and an uploaded file can be purchased online and are delivered instantly.')
            ->columns(3)
            ->schema([
                MoneyInput::make('price_cents')
                    ->label('Price')
                    ->helperText('Leave empty for free / enquiry-only products.'),
                MoneyInput::make('sale_price_cents')
                    ->label('Sale price')
                    ->rule(fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get): void {
                        $price = $get('price_cents');

                        if (filled($value) && (filled($price) ? (float) $value >= (float) $price : true)) {
                            $fail('The sale price must be lower than the regular price.');
                        }
                    }),
                Select::make('currency')
                    ->options(['USD' => 'USD — US Dollar'])
                    ->default('USD')
                    ->selectablePlaceholder(false)
                    ->required(),
                FileUpload::make('file_path')
                    ->label('Product file (PDF)')
                    ->helperText('Private. Only delivered to paying customers through expiring, signed links.')
                    ->disk(Book::PRIVATE_DISK)
                    ->directory('books/files')
                    ->visibility('private')
                    ->acceptedFileTypes(MediaUploader::DOCUMENT_MIMES)
                    ->maxSize(MediaUploader::MAX_DOCUMENT_KB)
                    ->storeFileNamesIn('file_original_name')
                    ->downloadable()
                    ->columnSpan(2),
                FileUpload::make('sample_path')
                    ->label('Free preview / sample (PDF)')
                    ->helperText('Optional. Publicly downloadable.')
                    ->disk('public')
                    ->directory('books/samples')
                    ->visibility('public')
                    ->acceptedFileTypes(MediaUploader::DOCUMENT_MIMES)
                    ->maxSize(MediaUploader::MAX_DOCUMENT_KB)
                    ->downloadable(),
                TextInput::make('external_purchase_url')
                    ->label('External purchase URL')
                    ->url()
                    ->maxLength(255)
                    ->helperText('If set, the “Buy” button links to this retailer instead of the on-site checkout.')
                    ->columnSpanFull(),
            ]);
    }

    private static function detailsSection(): Section
    {
        return Section::make('Publication details')
            ->icon('heroicon-o-identification')
            ->collapsible()
            ->columns(3)
            ->schema([
                TextInput::make('sku')->label('SKU')->maxLength(64)->unique(Book::class, 'sku', ignoreRecord: true),
                TextInput::make('isbn')->label('ISBN')->maxLength(32)->regex('/^[0-9Xx\-\s]{10,17}$/'),
                TextInput::make('format')->maxLength(64)->placeholder('PDF download'),
                TextInput::make('author')->maxLength(190),
                TagsInput::make('co_authors')->label('Co-authors')->placeholder('Add co-author')->columnSpan(2),
                TextInput::make('publisher')->maxLength(190),
                DatePicker::make('publication_date')->native(false),
                TextInput::make('edition')->maxLength(100),
                TextInput::make('language')->maxLength(32)->default('English')->required(),
                TextInput::make('page_count')->label('Number of pages')->numeric()->minValue(1)->maxValue(10000),
            ]);
    }

    private static function gallerySection(): Section
    {
        return Section::make('Additional images')
            ->icon('heroicon-o-photo')
            ->collapsible()
            ->collapsed()
            ->schema([
                Select::make('gallery')
                    ->label('Gallery')
                    ->relationship('gallery', 'title', fn ($query) => $query->images())
                    ->multiple()
                    ->allowHtml()
                    ->searchable(['title', 'alt', 'original_name'])
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn (Media $record): string => MediaPicker::optionHtml($record))
                    ->helperText('Interior pages or supporting visuals from the media library.'),
            ]);
    }

    private static function publishingSection(): Section
    {
        $canPublish = fn (): bool => (bool) Auth::user()?->can('publish', Book::class);

        return Section::make('Publishing')
            ->icon('heroicon-o-globe-alt')
            ->schema([
                Select::make('status')
                    ->options(BookStatus::class)
                    ->default(BookStatus::Draft)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->required()
                    ->disableOptionWhen(fn (string $value): bool => $value !== BookStatus::Draft->value && ! $canPublish())
                    ->helperText(fn (): ?string => $canPublish() ? null : 'Your role can save drafts. An administrator publishes products.'),
                DateTimePicker::make('published_at')
                    ->label('Publish date')
                    ->native(false)
                    ->seconds(false)
                    ->helperText('Schedule a future date, or leave empty to publish immediately.')
                    ->disabled(fn (): bool => ! $canPublish()),
                Toggle::make('is_featured')
                    ->label('Feature on homepage')
                    ->disabled(fn (): bool => ! $canPublish()),
                TextInput::make('sort_order')
                    ->label('Display order')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),
            ]);
    }

    private static function coverSection(): Section
    {
        return Section::make('Cover')
            ->icon('heroicon-o-photo')
            ->schema([
                MediaPicker::make('cover_id', 'covers')
                    ->label('Cover image')
                    ->live()
                    ->helperText('Portrait 2:3 artwork, at least 800px wide.'),
                TextEntry::make('cover_preview')
                    ->hiddenLabel()
                    ->visible(fn (Get $get): bool => filled($get('cover_id')))
                    ->state(fn (Get $get): ?HtmlString => ($media = Media::query()->find($get('cover_id')))
                        ? new HtmlString('<img src="'.e($media->url).'" alt="" class="w-full rounded-md shadow-sm ring-1 ring-slate-200">')
                        : null),
            ]);
    }

    private static function organizationSection(): Section
    {
        return Section::make('Organization')
            ->icon('heroicon-o-tag')
            ->schema([
                Select::make('categories')
                    ->relationship('categories', 'name', fn ($query) => $query->ordered())
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->alphaDash()->unique(BookCategory::class, 'slug'),
                    ]),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required()->alphaDash()->unique(BookTag::class, 'slug'),
                    ]),
            ]);
    }
}
