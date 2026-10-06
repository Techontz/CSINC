<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidAdminNotice extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->loadMissing('items');

        return (new MailMessage)
            ->subject('New order '.$order->number.' — '.Money::format($order->total_cents, $order->currency))
            ->line('Customer: '.e($order->customer_name).' <'.e($order->customer_email).'>')
            ->line('Items: '.e($order->items->pluck('title')->implode(', ')))
            ->action('Open order', url('/admin/orders/'.$order->getKey()));
    }
}
