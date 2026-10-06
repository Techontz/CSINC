<?php

namespace App\Filament\Widgets;

use App\Commerce\StripeGateway;
use App\Enums\BookStatus;
use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Highlights catalogue issues that stop products from being sold.
 */
class CatalogHealth extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.catalog-health';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->can('viewAny', Book::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $published = Book::query()->where('status', BookStatus::Published);

        return [
            'checks' => [
                [
                    'label' => 'Published products without a download file',
                    'count' => (clone $published)->whereNotNull('price_cents')->whereNull('file_path')->whereNull('external_purchase_url')->count(),
                    'help' => 'These show “Enquire” instead of “Buy” until a PDF is uploaded.',
                    'url' => BookResource::getUrl('index', ['activeTab' => 'missing_file']),
                ],
                [
                    'label' => 'Published products without a cover',
                    'count' => (clone $published)->whereNull('cover_id')->count(),
                    'help' => 'Covers drive click-through on listings and social shares.',
                    'url' => BookResource::getUrl('index'),
                ],
                [
                    'label' => 'Products missing a short description',
                    'count' => (clone $published)->where(fn ($q) => $q->whereNull('short_description')->orWhere('short_description', ''))->count(),
                    'help' => 'Used on cards and as the default search description.',
                    'url' => BookResource::getUrl('index'),
                ],
            ],
            'paymentsConfigured' => app(StripeGateway::class)->isConfigured(),
        ];
    }
}
