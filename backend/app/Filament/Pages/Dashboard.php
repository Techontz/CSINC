<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CatalogHealth;
use App\Filament\Widgets\LatestProducts;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\RecentMessages;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SystemInformation;
use App\Filament\Widgets\Welcome;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [
            Welcome::class,
            OverviewStats::class,
            SalesChart::class,
            CatalogHealth::class,
            LatestProducts::class,
            RecentMessages::class,
            RecentActivity::class,
            SystemInformation::class,
        ];
    }

    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 3];
    }
}
