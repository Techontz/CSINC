<?php

namespace Tests\Feature\Api;

use App\Commerce\StripeGateway;
use App\Enums\OrderStatus;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\DownloadLinksResent;
use App\Notifications\OrderReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CommerceTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    private function configureStripe(): void
    {
        config(['services.stripe.secret' => 'sk_test_123', 'services.stripe.webhook_secret' => self::WEBHOOK_SECRET]);
    }

    private function checkoutPayload(array $slugs): array
    {
        return ['items' => $slugs, 'name' => 'Grace Hopper', 'email' => 'Grace@Example.com', 'accept_terms' => true];
    }

    private function postWebhook(array $event, ?string $secret = self::WEBHOOK_SECRET): TestResponse
    {
        $payload = json_encode($event);

        return $this->call('POST', '/api/v1/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => StripeGateway::sign($payload, $secret ?? 'wrong'),
        ], $payload);
    }

    public function test_checkout_is_unavailable_until_stripe_is_configured(): void
    {
        $book = Book::factory()->published()->withFile()->create();

        $this->postJson('/api/v1/checkout', $this->checkoutPayload([$book->slug]))->assertStatus(503);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_creates_pending_order_and_stripe_session(): void
    {
        $this->configureStripe();
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1'])]);
        $first = Book::factory()->published()->withFile()->create(['price_cents' => 19700]);
        $second = Book::factory()->published()->withFile()->create(['price_cents' => 4700, 'sale_price_cents' => 2700]);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutPayload([$first->slug, $second->slug, $first->slug]));

        $response->assertCreated()->assertJsonPath('data.checkout_url', 'https://checkout.stripe.com/c/pay/cs_test_1');
        $order = Order::query()->with('items')->sole();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(22400, $order->total_cents);
        $this->assertSame('grace@example.com', $order->customer_email);
        $this->assertCount(2, $order->items);
        $this->assertSame('cs_test_1', $order->stripe_session_id);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
            && $request['client_reference_id'] === $order->uuid
            && $request['line_items'][1]['price_data']['unit_amount'] === 2700
            && str_contains($request['success_url'], $order->access_token));
    }

    public function test_checkout_rejects_products_that_cannot_be_bought(): void
    {
        $this->configureStripe();
        Http::fake();
        $withoutFile = Book::factory()->published()->create();
        $draft = Book::factory()->withFile()->create();

        $this->postJson('/api/v1/checkout', $this->checkoutPayload([$withoutFile->slug, $draft->slug]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);

        Http::assertNothingSent();
    }

    public function test_checkout_requires_terms_acceptance(): void
    {
        $this->configureStripe();
        $book = Book::factory()->published()->withFile()->create();

        $this->postJson('/api/v1/checkout', [...$this->checkoutPayload([$book->slug]), 'accept_terms' => false])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['accept_terms']);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $this->configureStripe();
        $order = Order::factory()->create(['stripe_session_id' => 'cs_test_1']);

        $this->postWebhook(['type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_1', 'payment_status' => 'paid']]], 'whsec_attacker')
            ->assertStatus(400);

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_completed_session_marks_order_paid_and_emails_downloads_once(): void
    {
        $this->configureStripe();
        Notification::fake();
        $book = Book::factory()->published()->withFile()->create();
        $order = Order::factory()->create(['stripe_session_id' => 'cs_test_1', 'customer_email' => 'grace@example.com']);
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);

        $event = ['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_test_1', 'client_reference_id' => $order->uuid, 'payment_status' => 'paid', 'payment_intent' => 'pi_123',
        ]]];

        $this->postWebhook($event)->assertOk();
        $this->postWebhook($event)->assertOk();

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('pi_123', $order->stripe_payment_intent);
        $this->assertNotNull($order->paid_at);
        Notification::assertSentTimes(OrderReceipt::class, 1);
        Notification::assertSentTo(new AnonymousNotifiable, OrderReceipt::class, fn ($n, $c, $notifiable): bool => $notifiable->routes['mail'] === 'grace@example.com');
    }

    public function test_expired_session_cancels_pending_order(): void
    {
        $this->configureStripe();
        $order = Order::factory()->create(['stripe_session_id' => 'cs_test_2']);

        $this->postWebhook(['type' => 'checkout.session.expired', 'data' => ['object' => ['id' => 'cs_test_2']]])->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_refund_revokes_download_access(): void
    {
        $this->configureStripe();
        $order = Order::factory()->paid()->create(['stripe_payment_intent' => 'pi_9']);

        $this->postWebhook(['type' => 'charge.refunded', 'data' => ['object' => ['refunded' => true, 'payment_intent' => 'pi_9']]])->assertOk();

        $this->assertSame(OrderStatus::Refunded, $order->fresh()->status);
    }

    public function test_order_page_requires_its_access_token(): void
    {
        $book = Book::factory()->published()->withFile()->create();
        $order = Order::factory()->paid()->create();
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);

        $this->getJson("/api/v1/orders/{$order->uuid}")->assertNotFound();
        $this->getJson("/api/v1/orders/{$order->uuid}?token=wrong")->assertNotFound();

        $response = $this->getJson("/api/v1/orders/{$order->uuid}?token={$order->access_token}")->assertOk();
        $response->assertJsonPath('data.status', 'paid');
        $this->assertStringContainsString('signature=', $response->json('data.items.0.download_url'));
        $this->assertStringNotContainsString('access_token', $response->getContent());
    }

    public function test_pending_orders_expose_no_download_links(): void
    {
        $book = Book::factory()->published()->withFile()->create();
        $order = Order::factory()->create();
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);

        $this->getJson("/api/v1/orders/{$order->uuid}?token={$order->access_token}")->assertJsonPath('data.items.0.download_url', null);
    }

    public function test_signed_download_streams_file_for_paid_orders_only(): void
    {
        $book = Book::factory()->published()->withFile()->create(['title' => 'Group Home Startup']);
        $paid = Order::factory()->paid()->create();
        $item = $paid->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);
        $pending = Order::factory()->create();
        $pendingItem = $pending->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);

        $this->get("/api/v1/downloads/{$item->id}")->assertForbidden();

        $response = $this->get(URL::temporarySignedRoute('api.v1.downloads.show', now()->addHour(), ['orderItem' => $item->id]));
        $response->assertOk()->assertDownload('group-home-startup.pdf');
        $this->assertSame(1, $item->fresh()->download_count);

        $this->get(URL::temporarySignedRoute('api.v1.downloads.show', now()->addHour(), ['orderItem' => $pendingItem->id]))->assertForbidden();
        $this->get(URL::temporarySignedRoute('api.v1.downloads.show', now()->subMinute(), ['orderItem' => $item->id]))->assertForbidden();
    }

    public function test_resend_downloads_never_reveals_whether_an_email_bought(): void
    {
        Notification::fake();
        $order = Order::factory()->paid()->create(['customer_email' => 'buyer@example.com']);
        $order->items()->create(['title' => 'Guide', 'unit_price_cents' => 100]);

        $known = $this->postJson('/api/v1/orders/resend-downloads', ['email' => 'buyer@example.com'])->assertOk()->json('message');
        $unknown = $this->postJson('/api/v1/orders/resend-downloads', ['email' => 'stranger@example.com'])->assertOk()->json('message');

        $this->assertSame($known, $unknown);
        Notification::assertSentTimes(DownloadLinksResent::class, 1);
    }

    public function test_order_item_snapshot_survives_product_deletion(): void
    {
        $book = Book::factory()->published()->create(['title' => 'Original Title']);
        $order = Order::factory()->paid()->create();
        $order->items()->create(['book_id' => $book->id, 'title' => $book->title, 'unit_price_cents' => 9700]);

        $book->forceDelete();

        $this->assertSame('Original Title', OrderItem::query()->sole()->title);
    }
}
