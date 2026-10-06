<?php

namespace App\Http\Controllers\Api\V1;

use App\Commerce\StripeGateway;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Models\Book;
use App\Models\Order;
use App\Settings\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, StripeGateway $stripe, SiteSettings $settings): JsonResponse
    {
        if (! $settings->get('commerce.enabled') || ! $stripe->isConfigured()) {
            return response()->json([
                'message' => 'Online checkout is temporarily unavailable. Please contact us to complete your purchase.',
            ], 503);
        }

        $slugs = $request->validated('items');
        $books = Book::query()->published()->whereIn('slug', $slugs)->get()->keyBy('slug');
        $unavailable = collect($slugs)->reject(fn (string $slug): bool => $books->has($slug) && $books[$slug]->isPurchasable());

        if ($unavailable->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Some items in your cart are no longer available: '.$unavailable->implode(', ').'. Please remove them and try again.',
            ]);
        }

        $currencies = $books->pluck('currency')->unique();

        if ($currencies->count() > 1) {
            throw ValidationException::withMessages(['items' => 'Items priced in different currencies must be purchased separately.']);
        }

        $order = DB::transaction(function () use ($request, $books, $currencies): Order {
            $total = $books->sum(fn (Book $book): int => (int) $book->effectivePriceCents());

            $order = Order::query()->create([
                'customer_name' => $request->validated('name'),
                'customer_email' => $request->validated('email'),
                'status' => OrderStatus::Pending,
                'subtotal_cents' => $total,
                'total_cents' => $total,
                'currency' => $currencies->first(),
                'terms_accepted_at' => now(),
                'ip_address' => $request->ip(),
            ]);

            $order->items()->createMany($books->values()->map(fn (Book $book): array => [
                'book_id' => $book->getKey(),
                'title' => $book->title,
                'sku' => $book->sku,
                'unit_price_cents' => (int) $book->effectivePriceCents(),
                'quantity' => 1,
            ])->all());

            return $order;
        });

        $frontend = rtrim((string) config('services.frontend.url'), '/');

        try {
            $session = $stripe->createCheckoutSession(
                $order,
                successUrl: $frontend.'/orders/'.$order->uuid.'?token='.$order->access_token.'&checkout=success',
                cancelUrl: $frontend.'/cart?checkout=cancelled',
            );
        } catch (Throwable $exception) {
            Log::error('Stripe checkout session failed', ['order' => $order->number, 'error' => $exception->getMessage()]);
            $order->forceFill(['status' => OrderStatus::Failed])->save();

            return response()->json([
                'message' => 'We could not start the secure payment session. Please try again in a moment.',
            ], 502);
        }

        $order->forceFill(['stripe_session_id' => $session['id']])->save();

        return response()->json(['data' => ['checkout_url' => $session['url'], 'order' => $order->uuid]], 201);
    }
}
