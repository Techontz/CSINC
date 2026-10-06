<?php

namespace App\Commerce;

use App\Enums\OrderStatus;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderPaidAdminNotice;
use App\Notifications\OrderReceipt;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Throwable;

class OrderFulfillment
{
    public function __construct(private SiteSettings $settings) {}

    /**
     * Marks an order paid and emails the customer their downloads. Idempotent.
     */
    public function markPaid(Order $order, ?string $paymentIntent = null): void
    {
        $transitioned = DB::transaction(function () use ($order, $paymentIntent): bool {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($locked->status === OrderStatus::Paid || $locked->status === OrderStatus::Refunded) {
                return false;
            }

            $locked->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'stripe_payment_intent' => $paymentIntent ?? $locked->stripe_payment_intent,
            ])->save();

            ActivityLog::record('paid', $locked, description: "Order {$locked->number} paid by {$locked->customer_email}");

            return true;
        });

        if (! $transitioned) {
            return;
        }

        $order->refresh();
        $this->sendDownloads($order);

        $admin = $this->settings->get('contact.notification_email') ?: config('services.notifications.admin_email');

        if (filled($admin)) {
            $this->safely(fn () => Notification::route('mail', $admin)->notify(new OrderPaidAdminNotice($order)));
        }
    }

    public function sendDownloads(Order $order): void
    {
        $this->safely(function () use ($order): void {
            Notification::route('mail', $order->customer_email)->notify(new OrderReceipt($order));
            $order->forceFill(['fulfilled_at' => now()])->save();
        });
    }

    public function markStatus(Order $order, OrderStatus $status): void
    {
        if ($order->status === OrderStatus::Paid && $status !== OrderStatus::Refunded) {
            return;
        }

        $order->forceFill(['status' => $status])->save();
    }

    public function downloadUrl(OrderItem $item): string
    {
        $hours = (int) $this->settings->get('commerce.download_expiry_hours') ?: 72;

        return URL::temporarySignedRoute('api.v1.downloads.show', now()->addHours($hours), ['orderItem' => $item->getKey()]);
    }

    public function orderUrl(Order $order): string
    {
        return rtrim((string) config('services.frontend.url'), '/').'/orders/'.$order->uuid.'?token='.$order->access_token;
    }

    private function safely(callable $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            Log::error('Order notification failed', ['error' => $exception->getMessage()]);
        }
    }
}
