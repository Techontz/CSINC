<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use Filament\Enums\GlobalSearchPosition;
use Filament\Enums\UserMenuPosition;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('CSinc91')
            ->brandLogo(fn () => view('filament.admin.brand'))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => [
                    50 => 'oklch(0.97 0.012 250)',
                    100 => 'oklch(0.94 0.024 250)',
                    200 => 'oklch(0.88 0.042 250)',
                    300 => 'oklch(0.78 0.068 250)',
                    400 => 'oklch(0.58 0.1 250)',
                    500 => 'oklch(0.42 0.1 250)',
                    600 => 'oklch(0.29 0.083 250)',
                    700 => 'oklch(0.25 0.075 251)',
                    800 => 'oklch(0.22 0.065 252)',
                    900 => 'oklch(0.19 0.055 253)',
                    950 => 'oklch(0.14 0.04 255)',
                ],
                'gray' => Color::Slate,
                'info' => Color::hex('#2b6cb0'),
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Inter')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(false)
            ->topbar(false)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('17rem')
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->spa()
            ->unsavedChangesAlerts()
            ->databaseNotifications(false)
            ->navigationGroups([
                NavigationGroup::make('Catalog'),
                NavigationGroup::make('Commerce'),
                NavigationGroup::make('Inbox'),
                NavigationGroup::make('Website'),
                NavigationGroup::make('Administration')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_END, fn (): string => Blade::render('filament.admin.sidebar-footer'))
            ->renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, fn (): string => Blade::render('filament.admin.auth-aside'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $component): void {
            $component->preventFilePathTampering();
        });
    }
}
