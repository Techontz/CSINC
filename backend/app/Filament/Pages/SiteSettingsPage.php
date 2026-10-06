<?php

namespace App\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Forms\MediaPicker;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Settings\SiteSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SiteSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 2;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && ($user->can(Permission::ManageSettings->value) || $user->can(Permission::ManageSeo->value));
    }

    public function getSubheading(): ?string
    {
        return 'Brand, contact details, social profiles, search defaults and checkout.';
    }

    public function mount(): void
    {
        $values = app(SiteSettings::class)->all();
        $nested = [];

        foreach ($values as $key => $value) {
            Arr::set($nested, $key, $value);
        }

        $this->form->fill($nested);
    }

    public function form(Schema $schema): Schema
    {
        $canManageSettings = fn (): bool => (bool) Auth::user()?->can(Permission::ManageSettings->value);

        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('General')
                            ->icon('heroicon-m-building-office-2')
                            ->visible($canManageSettings)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('site.name')->label('Site name')->required()->maxLength(80),
                                    TextInput::make('site.legal_name')->label('Legal name')->maxLength(120),
                                    TextInput::make('site.tagline')->label('Tagline')->maxLength(190)->columnSpanFull(),
                                    Textarea::make('site.description')->label('Description')->rows(3)->maxLength(500)->columnSpanFull(),
                                    MediaPicker::make('site.logo_id', 'brand')->label('Logo (dark, for light backgrounds)'),
                                    MediaPicker::make('site.logo_inverse_id', 'brand')->label('Logo (white, for dark backgrounds)'),
                                    Textarea::make('footer.statement')->label('Footer statement')->rows(2)->maxLength(300)->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Contact')
                            ->icon('heroicon-m-map-pin')
                            ->visible($canManageSettings)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('contact.email')->label('Public email')->email()->required(),
                                    TextInput::make('contact.support_email')->label('Support email')->email(),
                                    TextInput::make('contact.phone')->label('Phone')->tel()->maxLength(40),
                                    TextInput::make('contact.hours')->label('Office hours')->maxLength(120),
                                    TextInput::make('contact.address_line_1')->label('Address line 1')->maxLength(190),
                                    TextInput::make('contact.address_line_2')->label('Address line 2')->maxLength(190),
                                    TextInput::make('contact.city')->label('City')->maxLength(120),
                                    TextInput::make('contact.region')->label('State / region')->maxLength(120),
                                    TextInput::make('contact.postal_code')->label('Postal code')->maxLength(20),
                                    TextInput::make('contact.country')->label('Country')->maxLength(120),
                                    TextInput::make('contact.consultation_url')
                                        ->label('“Schedule a consultation” link')
                                        ->maxLength(255)
                                        ->regex('/^(https?:\/\/|mailto:|\/)/')
                                        ->helperText('A booking page URL, or a mailto: link.')
                                        ->columnSpanFull(),
                                    TextInput::make('contact.notification_email')
                                        ->label('Send new inquiries & orders to')
                                        ->email()
                                        ->required()
                                        ->helperText('Private — never shown on the website.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                        Tab::make('Social')
                            ->icon('heroicon-m-share')
                            ->visible($canManageSettings)
                            ->schema([
                                Repeater::make('social.links')
                                    ->hiddenLabel()
                                    ->schema([
                                        Select::make('platform')
                                            ->options(['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'youtube' => 'YouTube', 'tiktok' => 'TikTok'])
                                            ->required(),
                                        TextInput::make('url')->url()->required()->maxLength(255),
                                    ])
                                    ->columns(2)
                                    ->maxItems(8)
                                    ->addActionLabel('Add social profile')
                                    ->defaultItems(0),
                            ]),
                        Tab::make('Search & SEO')
                            ->icon('heroicon-m-magnifying-glass')
                            ->schema([
                                TextInput::make('seo.default_title')->label('Homepage / default title')->maxLength(190),
                                TextInput::make('seo.title_template')
                                    ->label('Title template')
                                    ->maxLength(80)
                                    ->helperText('%s is replaced by the page title, e.g. “%s — CSinc91”.'),
                                Textarea::make('seo.default_description')->label('Default meta description')->rows(3)->maxLength(320),
                                MediaPicker::make('seo.og_image_id', 'seo')->label('Default social sharing image')->helperText('1200×630 recommended.'),
                            ]),
                        Tab::make('Commerce')
                            ->icon('heroicon-m-shopping-bag')
                            ->visible($canManageSettings)
                            ->schema([
                                Toggle::make('commerce.enabled')
                                    ->label('Enable online checkout')
                                    ->helperText('When off, product pages invite visitors to enquire instead of buying.'),
                                TextInput::make('commerce.download_expiry_hours')
                                    ->label('Download link lifetime (hours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(720)
                                    ->required(),
                                Textarea::make('commerce.checkout_note')->label('Checkout note')->rows(3)->maxLength(500),
                            ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $allowed = Auth::user()->can(Permission::ManageSettings->value)
            ? array_keys(SiteSettings::DEFAULTS)
            : array_values(array_filter(array_keys(SiteSettings::DEFAULTS), fn (string $key): bool => str_starts_with($key, 'seo.')));

        $values = [];

        foreach ($allowed as $key) {
            if (Arr::has($state, $key)) {
                $values[$key] = Arr::get($state, $key);
            }
        }

        if (array_key_exists('social.links', $values)) {
            $values['social.links'] = array_values($values['social.links'] ?? []);
        }

        SiteSetting::putMany($values);
        ActivityLog::record('updated', null, ['keys' => array_keys($values)], 'updated site settings');

        Notification::make()->success()->title('Settings saved')->body('The website has been updated.')->send();
    }
}
