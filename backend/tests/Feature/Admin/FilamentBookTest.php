<?php

namespace Tests\Feature\Admin;

use App\Enums\BookStatus;
use App\Enums\Role;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Media;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentBookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_admin_creates_publishes_edits_and_unpublishes_a_product(): void
    {
        $admin = $this->userWithRole(Role::Admin);
        $this->actingAs($admin);
        $category = BookCategory::factory()->create();
        $cover = Media::factory()->create();

        Livewire::test(CreateBook::class)
            ->fillForm([
                'title' => 'Irrigation Service Startup',
                'slug' => 'irrigation-service-startup',
                'short_description' => 'Step-by-step startup guide — irrigation services.',
                'price_cents' => '97.00',
                'currency' => 'USD',
                'language' => 'English',
                'status' => BookStatus::Published->value,
                'categories' => [$category->id],
                'cover_id' => $cover->id,
                'file_path' => UploadedFile::fake()->create('irrigation.pdf', 120, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $book = Book::query()->where('slug', 'irrigation-service-startup')->sole();
        $this->assertSame(9700, $book->price_cents);
        $this->assertSame(BookStatus::Published, $book->status);
        $this->assertNotNull($book->published_at);
        $this->assertSame($cover->id, $book->cover_id);
        $this->assertSame([$category->id], $book->categories->modelKeys());
        Storage::disk('local')->assertExists($book->file_path);
        $this->assertSame('irrigation.pdf', $book->file_original_name);
        $this->assertTrue(ActivityLog::query()->where('event', 'created')->where('user_id', $admin->id)->exists());

        // Live on the public API.
        $this->getJson('/api/v1/books/irrigation-service-startup')->assertOk()->assertJsonPath('data.is_purchasable', true);

        // Edit.
        Livewire::test(EditBook::class, ['record' => $book->getRouteKey()])
            ->fillForm(['title' => 'Irrigation & Sprinkler Startup', 'sale_price_cents' => '79.00'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->getJson('/api/v1/books/irrigation-service-startup')
            ->assertJsonPath('data.title', 'Irrigation & Sprinkler Startup')
            ->assertJsonPath('data.price.formatted', '$79.00')
            ->assertJsonPath('data.price.on_sale', true);

        // Unpublish via header action.
        Livewire::test(EditBook::class, ['record' => $book->getRouteKey()])
            ->callAction('unpublish')
            ->assertNotified();

        $this->getJson('/api/v1/books/irrigation-service-startup')->assertNotFound();

        // Move to trash.
        Livewire::test(EditBook::class, ['record' => $book->getRouteKey()])->callAction(DeleteAction::class);
        $this->assertSoftDeleted($book);
    }

    public function test_form_validation_rules(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));
        Book::factory()->create(['slug' => 'taken-slug']);

        Livewire::test(CreateBook::class)
            ->fillForm([
                'title' => '',
                'slug' => 'taken-slug',
                'price_cents' => '50.00',
                'sale_price_cents' => '80.00',
                'file_path' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'slug' => 'unique', 'sale_price_cents', 'file_path']);
    }

    public function test_editor_cannot_publish_even_if_the_form_is_tampered_with(): void
    {
        $this->actingAs($this->userWithRole(Role::Editor));

        Livewire::test(CreateBook::class)
            ->fillForm([
                'title' => 'Editor Guide',
                'slug' => 'editor-guide',
                'currency' => 'USD',
                'language' => 'English',
                'status' => BookStatus::Published->value,
            ])
            ->call('create');

        $book = Book::query()->where('slug', 'editor-guide')->first();
        $this->assertTrue($book === null || $book->status === BookStatus::Draft);
        $this->assertFalse((bool) $book?->is_featured);
    }

    public function test_bulk_publish_from_the_table(): void
    {
        $this->actingAs($this->userWithRole(Role::Admin));
        $drafts = Book::factory()->count(3)->create();

        Livewire::test(ListBooks::class)
            ->assertCanSeeTableRecords($drafts)
            ->callTableBulkAction('publish', $drafts);

        $this->assertSame(3, Book::query()->where('status', BookStatus::Published)->count());
        $this->getJson('/api/v1/books')->assertJsonPath('meta.total', 3);
    }
}
