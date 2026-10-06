<?php

namespace App\Notifications;

use App\Commerce\OrderFulfillment;
use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent synchronously so signed download links are generated at send time.
 */
class OrderReceipt extends Notification
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
        $fulfillment = app(OrderFulfillment::class);
        $order = $this->order->loadMissing('items');

        $mail = (new MailMessage)
            ->subject('Your CSinc91 downloads — order '.$order->number)
            ->greeting('Thank you, '.e($order->customer_name).'.')
            ->line('Your payment of '.Money::format($order->total_cents, $order->currency).' was received. Your resources are ready to download:');

        foreach ($order->items as $item) {
            $mail->line('• ['.e($item->title).']('.$fulfillment->downloadUrl($item).')');
        }

        return $mail
            ->line('Download links expire for your security. You can always get fresh links from your order page:')
            ->action('View your order', $fulfillment->orderUrl($order))
            ->line('Practitioner support is available — simply reply to this email.');
    }
}
