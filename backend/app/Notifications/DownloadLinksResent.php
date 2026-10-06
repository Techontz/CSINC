<?php

namespace App\Notifications;

use App\Commerce\OrderFulfillment;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DownloadLinksResent extends Notification
{
    use Queueable;

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function __construct(public Collection $orders) {}

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

        $mail = (new MailMessage)
            ->subject('Your CSinc91 download links')
            ->line('As requested, here are fresh links to your purchased resources:');

        foreach ($this->orders as $order) {
            $mail->line('**Order '.$order->number.'**');

            foreach ($order->items as $item) {
                $mail->line('• ['.e($item->title).']('.$fulfillment->downloadUrl($item).')');
            }
        }

        return $mail->line('If you did not request this email, you can safely ignore it.');
    }
}
