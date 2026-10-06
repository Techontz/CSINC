<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Enums\MessageStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected ?string $subheading = 'Consultation requests submitted through the website contact form.';

    public function getTabs(): array
    {
        $counts = ContactMessage::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        $tabs = ['inbox' => Tab::make('Inbox')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', MessageStatus::Archived))
            ->badge($counts->except(MessageStatus::Archived->value)->sum() ?: null)];

        foreach (MessageStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts[$status->value] ?? null)
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
