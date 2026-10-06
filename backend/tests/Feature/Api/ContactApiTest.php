<?php

namespace Tests\Feature\Api;

use App\Enums\MessageStatus;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('forms-minute:127.0.0.1');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'Ada@Example.com',
            'phone' => '+1 (404) 555-0100',
            'address' => ['city' => 'Atlanta', 'country' => 'United States'],
            'industry' => 'Healthcare-related',
            'growth_stage' => 'Startup',
            'challenges' => ['Strategy Development', 'Market Positioning'],
            'primary_goal' => 'Revenue',
            'message' => 'I want to open a licensed group home in Georgia.',
            'preferred_start_date' => now()->addWeek()->toDateString(),
            'preferred_service' => 'Business Formation',
            'signature' => 'Ada Lovelace',
            'consent' => true,
            'website' => '',
            'started_at' => (int) ((microtime(true) - 30) * 1000),
        ], $overrides);
    }

    public function test_valid_inquiry_is_stored_and_staff_are_notified(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/contact', $this->payload())->assertCreated()->assertJsonStructure(['message']);

        $message = ContactMessage::query()->sole();
        $this->assertSame('ada@example.com', $message->email);
        $this->assertSame(MessageStatus::New, $message->status);
        $this->assertSame(['Strategy Development', 'Market Positioning'], $message->details['challenges']);
        $this->assertSame('Atlanta, United States', $message->details['address']);

        Notification::assertSentTo(new AnonymousNotifiable, ContactMessageReceived::class, function ($notification, $channels, $notifiable): bool {
            return $notifiable->routes['mail'] === 'Info@CSinc91.com';
        });
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/api/v1/contact', ['started_at' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'phone', 'growth_stage', 'challenges', 'message', 'preferred_start_date', 'signature', 'consent', 'address.city', 'address.country']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_invalid_choices_and_past_dates_are_rejected(): void
    {
        $this->postJson('/api/v1/contact', $this->payload([
            'growth_stage' => 'Unicorn',
            'challenges' => ['Hacking'],
            'preferred_start_date' => now()->subDay()->toDateString(),
            'email' => 'not-an-email',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['growth_stage', 'challenges.0', 'preferred_start_date', 'email']);
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['website' => 'https://spam.example']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['website']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_instant_submissions_are_rejected_as_bots(): void
    {
        $this->postJson('/api/v1/contact', $this->payload(['started_at' => (int) (microtime(true) * 1000)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['form']);
    }

    public function test_submissions_are_rate_limited(): void
    {
        Notification::fake();

        foreach (range(1, 3) as $attempt) {
            $this->postJson('/api/v1/contact', $this->payload())->assertCreated();
        }

        $this->postJson('/api/v1/contact', $this->payload())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 3);
    }

    public function test_form_options_are_published(): void
    {
        $this->getJson('/api/v1/contact/options')
            ->assertOk()
            ->assertJsonPath('data.growth_stages', ['Startup', 'Growth Phase', 'Mature', 'Scaling']);
    }
}
