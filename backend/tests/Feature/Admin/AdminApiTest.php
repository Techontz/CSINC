<?php

namespace Tests\Feature\Admin;

use App\Enums\BookStatus;
use App\Enums\MessageStatus;
use App\Enums\Role;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_obtain_a_token_with_valid_credentials(): void
    {
        $user = $this->userWithRole(Role::Admin, ['email' => 'admin@example.com', 'password' => 'correct-horse-battery']);

        $token = $this->postJson('/api/v1/admin/auth/login', ['email' => 'admin@example.com', 'password' => 'correct-horse-battery'])
            ->assertOk()
            ->assertJsonPath('data.user.roles', ['admin'])
            ->json('data.token');

        $this->withToken($token)->getJson('/api/v1/admin/auth/me')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_rejects_bad_credentials_and_non_staff(): void
    {
        $this->userWithRole(Role::Admin, ['email' => 'admin@example.com', 'password' => 'correct-horse-battery']);
        User::factory()->create(['email' => 'customer@example.com', 'password' => 'secret-password']);

        $this->postJson('/api/v1/admin/auth/login', ['email' => 'admin@example.com', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/v1/admin/auth/login', ['email' => 'customer@example.com', 'password' => 'secret-password'])->assertUnprocessable();
    }

    public function test_login_is_rate_limited(): void
    {
        RateLimiter::clear('login:admin@example.com|127.0.0.1');

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/admin/auth/login', ['email' => 'admin@example.com', 'password' => 'guess-'.$attempt])->assertUnprocessable();
        }

        $this->postJson('/api/v1/admin/auth/login', ['email' => 'admin@example.com', 'password' => 'guess'])->assertTooManyRequests();
    }

    public function test_admin_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/books')->assertUnauthorized();
        $this->postJson('/api/v1/admin/books', ['title' => 'X'])->assertUnauthorized();
    }

    public function test_full_book_lifecycle_through_the_admin_api(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Admin), ['admin']);
        $category = BookCategory::factory()->create();

        // Create a draft with a cover and downloadable file.
        $id = $this->post('/api/v1/admin/books', [
            'title' => 'Medical Courier Startup',
            'short_description' => 'Step-by-step startup guide — medical courier logistics.',
            'price_cents' => 9700,
            'category_ids' => [$category->id],
            'tags' => ['Logistics', 'Healthcare'],
            'cover' => UploadedFile::fake()->image('cover.jpg', 800, 1200),
            'file' => UploadedFile::fake()->create('guide.pdf', 200, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'medical-courier-startup')
            ->assertJsonPath('data.status', 'draft')
            ->json('data.id');

        $book = Book::query()->findOrFail($id);
        Storage::disk('public')->assertExists($book->cover->path);
        Storage::disk('local')->assertExists($book->file_path);
        $this->assertSame(['Healthcare', 'Logistics'], $book->tags->pluck('name')->sort()->values()->all());

        // Not public while a draft.
        $this->getJson('/api/v1/books/medical-courier-startup')->assertNotFound();

        // Publish → public, purchasable, with SEO metadata derived from content.
        $this->postJson("/api/v1/admin/books/{$id}/publish")->assertOk()->assertJsonPath('data.status', 'published');
        $this->getJson('/api/v1/books/medical-courier-startup')
            ->assertOk()
            ->assertJsonPath('data.is_purchasable', true)
            ->assertJsonPath('data.seo.title', 'Medical Courier Startup')
            ->assertJsonPath('data.seo.description', 'Step-by-step startup guide — medical courier logistics.');

        // Edit → public site reflects changes.
        $this->patchJson("/api/v1/admin/books/{$id}", ['title' => 'Medical Courier Business Startup', 'seo_title' => 'Start a Medical Courier Business'])->assertOk();
        $this->getJson('/api/v1/books/medical-courier-startup')
            ->assertJsonPath('data.title', 'Medical Courier Business Startup')
            ->assertJsonPath('data.seo.title', 'Start a Medical Courier Business');

        // Unpublish → disappears from the public site.
        $this->postJson("/api/v1/admin/books/{$id}/unpublish")->assertOk();
        $this->getJson('/api/v1/books/medical-courier-startup')->assertNotFound();
        $this->getJson('/api/v1/books')->assertJsonCount(0, 'data');

        // Delete → soft deleted.
        $this->deleteJson("/api/v1/admin/books/{$id}")->assertNoContent();
        $this->assertSoftDeleted('books', ['id' => $id]);
    }

    public function test_book_validation(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Admin), ['admin']);
        Book::factory()->create(['slug' => 'taken']);

        $this->postJson('/api/v1/admin/books', [
            'slug' => 'taken',
            'price_cents' => 1000,
            'sale_price_cents' => 5000,
            'isbn' => 'abc',
            'external_purchase_url' => 'javascript:alert(1)',
            'status' => 'live',
        ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'slug', 'sale_price_cents', 'isbn', 'external_purchase_url', 'status']);
    }

    public function test_uploads_are_validated_by_type_and_size(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Admin), ['admin']);

        $this->post('/api/v1/admin/books', [
            'title' => 'Bad Uploads',
            'cover' => UploadedFile::fake()->create('cover.php', 10, 'application/x-php'),
            'file' => UploadedFile::fake()->create('guide.exe', 10, 'application/x-msdownload'),
            'sample' => UploadedFile::fake()->create('huge.pdf', 60000, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors(['cover', 'file', 'sample']);
    }

    public function test_editor_can_draft_but_not_publish_or_delete(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Editor), ['admin']);

        $id = $this->postJson('/api/v1/admin/books', ['title' => 'Editor Draft'])->assertCreated()->json('data.id');

        $this->postJson('/api/v1/admin/books', ['title' => 'Sneaky', 'status' => 'published'])->assertForbidden();
        $this->patchJson("/api/v1/admin/books/{$id}", ['is_featured' => true])->assertForbidden();
        $this->postJson("/api/v1/admin/books/{$id}/publish")->assertForbidden();
        $this->deleteJson("/api/v1/admin/books/{$id}")->assertForbidden();

        $this->assertSame(BookStatus::Draft, Book::query()->find($id)->status);
    }

    public function test_tokens_without_admin_ability_are_rejected(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::SuperAdmin), ['read-only']);

        $this->getJson('/api/v1/admin/books')->assertForbidden();
    }

    public function test_category_management_and_permissions(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Admin), ['admin']);

        $id = $this->postJson('/api/v1/admin/book-categories', ['name' => 'Childcare', 'slug' => 'childcare', 'sort_order' => 3])
            ->assertCreated()->json('data.id');
        $this->patchJson("/api/v1/admin/book-categories/{$id}", ['is_published' => false])->assertOk();
        $this->getJson('/api/v1/book-categories')->assertJsonCount(0, 'data');
        $this->deleteJson("/api/v1/admin/book-categories/{$id}")->assertNoContent();
    }

    public function test_contact_message_status_workflow(): void
    {
        $admin = $this->userWithRole(Role::Admin);
        Sanctum::actingAs($admin, ['admin']);
        $message = ContactMessage::factory()->create();

        $this->getJson('/api/v1/admin/contact-messages?status=new')->assertOk()->assertJsonPath('total', 1);
        $this->patchJson("/api/v1/admin/contact-messages/{$message->id}", ['status' => 'replied', 'internal_notes' => 'Called back'])
            ->assertOk()
            ->assertJsonPath('data.status', 'replied');

        $message->refresh();
        $this->assertSame(MessageStatus::Replied, $message->status);
        $this->assertNotNull($message->read_at);
        $this->assertNotNull($message->replied_at);
        $this->assertSame($admin->id, $message->handled_by);
    }

    public function test_editor_can_read_but_not_update_messages(): void
    {
        Sanctum::actingAs($this->userWithRole(Role::Editor), ['admin']);
        $message = ContactMessage::factory()->create();

        $this->getJson("/api/v1/admin/contact-messages/{$message->id}")->assertOk();
        $this->patchJson("/api/v1/admin/contact-messages/{$message->id}", ['status' => 'archived'])->assertForbidden();
    }
}
