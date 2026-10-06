<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RecentMessages extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.recent-messages';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->can('viewAny', ContactMessage::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'messages' => ContactMessage::query()->latest()->limit(5)->get(),
            'indexUrl' => ContactMessageResource::getUrl('index'),
            'viewUrl' => fn (ContactMessage $message): string => ContactMessageResource::getUrl('view', ['record' => $message]),
        ];
    }
}
