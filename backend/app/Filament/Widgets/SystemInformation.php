<?php

namespace App\Filament\Widgets;

use App\Commerce\StripeGateway;
use App\Enums\Role;
use Filament\Widgets\Widget;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemInformation extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.system-information';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->hasAnyRole([Role::SuperAdmin->value, Role::Admin->value]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $media = Cache::remember('dashboard.media-usage', now()->addMinutes(10), function (): array {
            $files = collect(Storage::disk('public')->allFiles('media'));

            return ['count' => $files->count(), 'bytes' => $files->sum(fn (string $path): int => Storage::disk('public')->size($path))];
        });

        return [
            'items' => [
                'Environment' => ucfirst(app()->environment()),
                'Laravel' => Application::VERSION,
                'PHP' => PHP_VERSION,
                'Payments' => app(StripeGateway::class)->isConfigured() ? 'Stripe connected' : 'Not configured',
                'Mail driver' => config('mail.default'),
                'Queue' => config('queue.default'),
                'Media files' => $media['count'].' · '.number_format($media['bytes'] / 1_048_576, 1).' MB',
                'Debug mode' => config('app.debug') ? 'On' : 'Off',
            ],
        ];
    }
}
