<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RecentActivity extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.widgets.recent-activity';

    public static function canView(): bool
    {
        return (bool) Auth::user()?->can('viewAny', ActivityLog::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'entries' => ActivityLog::query()->with('user')->latest('created_at')->limit(8)->get(),
        ];
    }
}
