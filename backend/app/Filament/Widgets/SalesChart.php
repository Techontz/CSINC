<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class SalesChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Sales';

    protected ?string $description = 'Paid orders, last 30 days';

    protected int|string|array $columnSpan = ['lg' => 2];

    protected ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->can('viewAny', Order::class);
    }

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $totals = Order::query()
            ->where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $start)
            ->get(['paid_at', 'total_cents'])
            ->groupBy(fn (Order $order): string => $order->paid_at->toDateString())
            ->map(fn ($orders): float => $orders->sum('total_cents') / 100);

        $labels = [];
        $values = [];

        for ($day = $start->copy(); $day->lte(now()); $day->addDay()) {
            $labels[] = $day->format('M j');
            $values[] = round((float) ($totals[$day->toDateString()] ?? 0), 2);
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (USD)',
                'data' => $values,
                'borderColor' => '#00294c',
                'backgroundColor' => 'rgba(0, 41, 76, 0.06)',
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 0,
                'borderWidth' => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(15,23,42,0.05)'], 'ticks' => ['maxTicksLimit' => 5]],
                'x' => ['grid' => ['display' => false], 'ticks' => ['maxTicksLimit' => 8]],
            ],
        ];
    }
}
