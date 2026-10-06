<?php

namespace App\Http\Controllers\Api\V1;

use App\Commerce\OrderFulfillment;
use App\Commerce\StripeGateway;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeGateway $stripe, OrderFulfillment $fulfillment): JsonResponse
    {
        try {
            $event = $stripe->constructEvent($request->getContent(), $request->header('Stripe-Signature'));
        } catch (RuntimeException $exception) {
            Log::warning('Rejected Stripe webhook', ['reason' => $exception->getMessage()]);

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $object = $event['data']['object'] ?? [];

        match ($event['type']) {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => $this->whenOrder($object, function (Order $order) use ($object, $fulfillment): void {
                if (($object['payment_status'] ?? null) === 'paid') {
                    $fulfillment->markPaid($order, $object['payment_intent'] ?? null);
                }
            }),
            'checkout.session.async_payment_failed' => $this->whenOrder($object, fn (Order $order) => $fulfillment->markStatus($order, OrderStatus::Failed)),
            'checkout.session.expired' => $this->whenOrder($object, fn (Order $order) => $fulfillment->markStatus($order, OrderStatus::Cancelled)),
            'charge.refunded' => $this->refund($object, $fulfillment),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function whenOrder(array $session, callable $callback): void
    {
        $order = Order::query()
            ->where('stripe_session_id', $session['id'] ?? '')
            ->orWhere('uuid', $session['client_reference_id'] ?? '')
            ->first();

        if ($order) {
            $callback($order);
        }
    }

    /**
     * @param  array<string, mixed>  $charge
     */
    private function refund(array $charge, OrderFulfillment $fulfillment): void
    {
        if (! ($charge['refunded'] ?? false) || blank($charge['payment_intent'] ?? null)) {
            return;
        }

        $order = Order::query()->where('stripe_payment_intent', $charge['payment_intent'])->first();

        if ($order) {
            $fulfillment->markStatus($order, OrderStatus::Refunded);
        }
    }
}
