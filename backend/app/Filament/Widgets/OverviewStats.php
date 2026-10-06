<?php

namespace App\Filament\Widgets;

use App\Enums\BookStatus;
use App\Enums\MessageStatus;
use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OverviewStats extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $pollingInterval = null;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $statusCounts = Book::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
        $user = Auth::user();

        $stats = [
            Stat::make('Total products', (string) $statusCounts->sum())
                ->description(($statusCounts[BookStatus::Archived->value] ?? 0).' archived')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('gray'),
            Stat::make('Published', (string) ($statusCounts[BookStatus::Published->value] ?? 0))
                ->description('Live on the website')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('success'),
            Stat::make('Drafts', (string) ($statusCounts[BookStatus::Draft->value] ?? 0))
                ->description('Awaiting publication')
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('gray'),
        ];

        if ($user->can('viewAny', Order::class)) {
            $paid = Order::query()->where('status', OrderStatus::Paid)->where('paid_at', '>=', now()->subDays(30));
            $revenue = (clone $paid)->sum('total_cents');

            $stats[] = Stat::make('Revenue · 30 days', Money::format((int) $revenue) ?? '$0.00')
                ->description((clone $paid)->count().' paid orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success');
        } elseif ($user->can('viewAny', ContactMessage::class)) {
            $stats[] = Stat::make('New messages', (string) ContactMessage::query()->where('status', MessageStatus::New)->count())
                ->description('Unread inquiries')
                ->descriptionIcon('heroicon-m-inbox')
                ->color('info');
        }

        return $stats;
    }
}
