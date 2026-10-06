<?php

namespace Tests\Feature\Api;

use App\Enums\BookStatus;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_only_returns_published_products(): void
    {
        $visible = Book::factory()->published()->create(['title' => 'Group Home Startup']);
        Book::factory()->create(['title' => 'Draft Guide']);
        Book::factory()->archived()->create(['title' => 'Archived Guide']);
        Book::factory()->published()->create(['title' => 'Scheduled Guide', 'published_at' => now()->addWeek()]);
        Book::factory()->published()->create(['title' => 'Trashed Guide'])->delete();

        $response = $this->getJson('/api/v1/books');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $visible->slug)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_listing_never_exposes_private_file_paths(): void
    {
        Book::factory()->published()->withFile()->create();

        $json = $this->getJson('/api/v1/books')->assertOk()->getContent();

        $this->assertStringNotContainsString('books/files', $json);
        $this->assertStringNotContainsString('file_path', $json);
    }

    public function test_listing_filters_by_category_search_and_featured(): void
    {
        $healthcare = BookCategory::factory()->create(['slug' => 'healthcare']);
        $hospice = Book::factory()->published()->featured()->create(['title' => 'Hospice Care Agency Startup']);
        $hospice->categories()->attach($healthcare);
        Book::factory()->published()->create(['title' => 'Janitorial Startup']);

        $this->getJson('/api/v1/books?category=healthcare')->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Hospice Care Agency Startup');
        $this->getJson('/api/v1/books?search=janitorial')->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Janitorial Startup');
        $this->getJson('/api/v1/books?featured=1')->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Hospice Care Agency Startup');
    }

    public function test_listing_sorts_by_price(): void
    {
        Book::factory()->published()->create(['title' => 'Expensive', 'price_cents' => 99700]);
        Book::factory()->published()->create(['title' => 'Cheap', 'price_cents' => 4700]);

        $this->getJson('/api/v1/books?sort=price_asc')->assertJsonPath('data.0.title', 'Cheap');
        $this->getJson('/api/v1/books?sort=price_desc')->assertJsonPath('data.0.title', 'Expensive');
    }

    public function test_listing_validates_query_parameters(): void
    {
        $this->getJson('/api/v1/books?sort=drop-table&per_page=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'per_page']);
    }

    public function test_detail_returns_full_product_with_seo_and_related(): void
    {
        $category = BookCategory::factory()->create();
        $cover = Media::factory()->create(['alt' => 'Cover art']);
        $book = Book::factory()->published()->withFile()->create([
            'title' => 'Group Home Startup',
            'short_description' => 'Step-by-step startup guide.',
            'description' => '<p>Safe</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
            'cover_id' => $cover->id,
            'price_cents' => 59700,
        ]);
        $book->categories()->attach($category);
        $related = Book::factory()->published()->create();
        $related->categories()->attach($category);

        $response = $this->getJson("/api/v1/books/{$book->slug}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Group Home Startup')
            ->assertJsonPath('data.price.formatted', '$597.00')
            ->assertJsonPath('data.is_purchasable', true)
            ->assertJsonPath('data.seo.title', 'Group Home Startup')
            ->assertJsonPath('data.seo.description', 'Step-by-step startup guide.')
            ->assertJsonPath('data.seo.image.alt', 'Cover art')
            ->assertJsonPath('related.0.slug', $related->slug);

        $description = $response->json('data.description');
        $this->assertStringContainsString('<p>Safe</p>', $description);
        $this->assertStringNotContainsString('<script', $description);
        $this->assertStringNotContainsString('javascript:', $description);
    }

    public function test_product_without_file_is_not_purchasable(): void
    {
        $book = Book::factory()->published()->create(['price_cents' => 9700]);

        $this->getJson("/api/v1/books/{$book->slug}")->assertJsonPath('data.is_purchasable', false);
    }

    public function test_detail_returns_404_for_unpublished_product(): void
    {
        $draft = Book::factory()->create(['status' => BookStatus::Draft]);

        $this->getJson("/api/v1/books/{$draft->slug}")->assertNotFound();
        $this->getJson('/api/v1/books/does-not-exist')->assertNotFound();
    }

    public function test_categories_list_published_categories_with_live_counts(): void
    {
        $category = BookCategory::factory()->create(['sort_order' => 1]);
        BookCategory::factory()->unpublished()->create();
        $category->books()->attach(Book::factory()->published()->count(2)->create());
        $category->books()->attach(Book::factory()->create());

        $this->getJson('/api/v1/book-categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.books_count', 2);

        $this->getJson("/api/v1/book-categories/{$category->slug}")->assertOk()->assertJsonPath('data.slug', $category->slug);
    }

    public function test_sitemap_lists_only_public_urls(): void
    {
        $published = Book::factory()->published()->create();
        $draft = Book::factory()->create();

        $slugs = collect($this->getJson('/api/v1/sitemap')->assertOk()->json('data.books'))->pluck('slug');

        $this->assertTrue($slugs->contains($published->slug));
        $this->assertFalse($slugs->contains($draft->slug));
    }
}
