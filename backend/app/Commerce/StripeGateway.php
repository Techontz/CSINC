<?php

namespace App\Commerce;

use App\Models\Order;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal Stripe Checkout integration over the REST API.
 *
 * Payment details are collected exclusively on Stripe's hosted page, so card
 * data never touches this application.
 */
class StripeGateway
{
    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret')) && filled(config('services.stripe.webhook_secret'));
    }

    /**
     * @return array{id: string, url: string}
     *
     * @throws RequestException
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): array
    {
        $order->loadMissing('items');

        $payload = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $order->customer_email,
            'client_reference_id' => $order->uuid,
            'metadata' => ['order_uuid' => $order->uuid, 'order_number' => $order->number],
            'payment_intent_data' => ['metadata' => ['order_uuid' => $order->uuid, 'order_number' => $order->number]],
            'expires_at' => now()->addMinutes(60)->getTimestamp(),
            'line_items' => $order->items->map(fn ($item): array => [
                'quantity' => $item->quantity,
                'price_data' => [
                    'currency' => strtolower($order->currency),
                    'unit_amount' => $item->unit_price_cents,
                    'product_data' => array_filter([
                        'name' => $item->title,
                        'metadata' => array_filter(['sku' => $item->sku]),
                    ]),
                ],
            ])->all(),
        ];

        $response = Http::asForm()
            ->withToken((string) config('services.stripe.secret'))
            ->withHeaders(['Idempotency-Key' => 'checkout-'.$order->uuid])
            ->timeout(15)
            ->post(config('services.stripe.api_base').'/checkout/sessions', $payload)
            ->throw();

        return ['id' => (string) $response->json('id'), 'url' => (string) $response->json('url')];
    }

    /**
     * Verifies the Stripe-Signature header and returns the decoded event.
     *
     * @return array<string, mixed>
     */
    public function constructEvent(string $payload, ?string $signatureHeader): array
    {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '' || blank($signatureHeader)) {
            throw new RuntimeException('Missing webhook signature.');
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1' && $value) {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || $signatures === []) {
            throw new RuntimeException('Malformed webhook signature.');
        }

        if (abs(time() - $timestamp) > (int) config('services.stripe.webhook_tolerance', 300)) {
            throw new RuntimeException('Webhook timestamp outside tolerance.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                $event = json_decode($payload, true);

                if (! is_array($event) || ! isset($event['type'])) {
                    throw new RuntimeException('Invalid webhook payload.');
                }

                return $event;
            }
        }

        throw new RuntimeException('Webhook signature mismatch.');
    }

    public static function sign(string $payload, string $secret, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }
}
