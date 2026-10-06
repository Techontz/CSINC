<?php

namespace App\Filament\Resources\ContactMessages\Concerns;

use App\Enums\MessageStatus;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MessageActions
{
    public static function setStatus(MessageStatus $status, string $label, string $icon): Action
    {
        return Action::make('mark_'.$status->value)
            ->label($label)
            ->icon($icon)
            ->authorize(fn (ContactMessage $record): bool => Auth::user()->can('update', $record))
            ->visible(fn (ContactMessage $record): bool => $record->status !== $status)
            ->action(function (ContactMessage $record) use ($status): void {
                $record->markAs($status, Auth::user());
                Notification::make()->success()->title('Marked as '.$status->getLabel())->send();
            });
    }
}
