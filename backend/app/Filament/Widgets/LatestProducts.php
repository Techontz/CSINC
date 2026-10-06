<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class LatestProducts extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.latest-products';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->can('viewAny', Book::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'books' => Book::query()->with('cover')->latest('updated_at')->limit(5)->get(),
            'indexUrl' => BookResource::getUrl('index'),
            'editUrl' => fn (Book $book): string => BookResource::getUrl('edit', ['record' => $book]),
        ];
    }
}
