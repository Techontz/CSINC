<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Concerns\GuardsPublishing;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    use GuardsPublishing;

    protected static string $resource = BookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->guardPublishing($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
