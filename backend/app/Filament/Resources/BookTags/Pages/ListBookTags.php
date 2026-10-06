<?php

namespace App\Filament\Resources\BookTags\Pages;

use App\Filament\Resources\BookTags\BookTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookTags extends ListRecords
{
    protected static string $resource = BookTagResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New tag')->icon('heroicon-m-plus')];
    }
}
