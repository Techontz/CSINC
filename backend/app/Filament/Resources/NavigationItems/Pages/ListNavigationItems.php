<?php

namespace App\Filament\Resources\NavigationItems\Pages;

use App\Filament\Resources\NavigationItems\NavigationItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNavigationItems extends ListRecords
{
    protected static string $resource = NavigationItemResource::class;

    protected ?string $subheading = 'Manage the main menu, footer menu and legal links. Changes appear on the website immediately.';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New menu item')->icon('heroicon-m-plus')];
    }
}
