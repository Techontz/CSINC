<?php

namespace App\Filament\Widgets;

use App\Enums\MessageStatus;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Page;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class Welcome extends Widget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();
        $hour = (int) now()->format('G');

        $actions = array_values(array_filter([
            $user->can('create', Book::class) ? ['label' => 'New product', 'icon' => 'heroicon-o-plus', 'url' => BookResource::getUrl('create')] : null,
            $user->can('viewAny', ContactMessage::class) ? [
                'label' => 'Inbox',
                'icon' => 'heroicon-o-inbox',
                'url' => ContactMessageResource::getUrl('index'),
                'badge' => ContactMessage::query()->where('status', MessageStatus::New)->count() ?: null,
            ] : null,
            $user->can('viewAny', Page::class) ? ['label' => 'Edit homepage', 'icon' => 'heroicon-o-home', 'url' => PageResource::getUrl('edit', ['record' => Page::query()->where('slug', Page::HOME_SLUG)->value('id') ?? 0])] : null,
            $user->can('viewAny', Media::class) ? ['label' => 'Media library', 'icon' => 'heroicon-o-photo', 'url' => MediaResource::getUrl('index')] : null,
        ]));

        return [
            'greeting' => match (true) {
                $hour < 12 => 'Good morning',
                $hour < 18 => 'Good afternoon',
                default => 'Good evening',
            },
            'name' => str($user->name)->before(' ')->toString(),
            'date' => now()->format('l, F j'),
            'actions' => $actions,
        ];
    }
}
