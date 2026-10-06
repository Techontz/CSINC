<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Commerce\OrderFulfillment;
use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\ActivityLog;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

/**
 * @property Order $record
 */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Order '.$this->record->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend')
                ->label('Resend download links')
                ->icon('heroicon-m-paper-airplane')
                ->visible(fn (): bool => $this->record->status === OrderStatus::Paid)
                ->authorize(fn (): bool => Auth::user()->can('update', $this->record))
                ->requiresConfirmation()
                ->modalDescription(fn (): string => 'Fresh, expiring download links will be emailed to '.$this->record->customer_email.'.')
                ->action(function (OrderFulfillment $fulfillment): void {
                    $fulfillment->sendDownloads($this->record);
                    ActivityLog::record('resent', $this->record, description: 'resent downloads for order '.$this->record->number);
                    Notification::make()->success()->title('Download links sent')->send();
                }),
            Action::make('refund')
                ->label('Mark as refunded')
                ->icon('heroicon-m-arrow-uturn-left')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === OrderStatus::Paid)
                ->authorize(fn (): bool => Auth::user()->can('update', $this->record))
                ->requiresConfirmation()
                ->modalDescription('Revokes download access. Issue the refund itself from your Stripe dashboard.')
                ->action(function (): void {
                    $this->record->update(['status' => OrderStatus::Refunded]);
                    ActivityLog::record('refunded', $this->record, description: 'marked order '.$this->record->number.' as refunded');
                    Notification::make()->success()->title('Order marked as refunded')->send();
                }),
            Action::make('note')
                ->label('Notes')
                ->icon('heroicon-m-pencil-square')
                ->color('gray')
                ->authorize(fn (): bool => Auth::user()->can('update', $this->record))
                ->fillForm(fn (): array => ['internal_notes' => $this->record->internal_notes])
                ->schema([Textarea::make('internal_notes')->rows(5)->maxLength(5000)])
                ->action(fn (array $data) => $this->record->update(['internal_notes' => $data['internal_notes']])),
        ];
    }
}
