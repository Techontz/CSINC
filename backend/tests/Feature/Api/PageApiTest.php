<?php

namespace Tests\Feature\Api;

use App\Enums\ServiceGroup;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Media;
use App\Models\Page;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_page_returns_hydrated_blocks(): void
    {
        $image = Media::factory()->create(['alt' => 'Hero image']);
        $category = BookCategory::factory()->create(['slug' => 'healthcare']);
        $category->books()->attach(Book::factory()->published()->count(3)->create());
        Book::factory()->published()->featured()->create(['title' => 'Featured Guide']);
        Service::factory()->create(['group' => ServiceGroup::Business, 'title' => 'Business Formation']);

        Page::factory()->create([
            'slug' => 'home',
            'blocks' => [
                ['type' => 'hero', 'data' => ['heading' => 'Hello', 'image_id' => $image->id]],
                ['type' => 'stats', 'data' => ['heading' => 'Numbers', 'items' => [
                    ['value' => '{{products.count}}', 'label' => 'Guides'],
                    ['value' => '{{category.healthcare.count}}', 'label' => 'Healthcare'],
                ]]],
                ['type' => 'books', 'data' => ['heading' => 'Featured', 'source' => 'featured']],
                ['type' => 'services', 'data' => ['heading' => 'Services', 'group' => 'business']],
                ['type' => 'rich_text', 'data' => ['body_html' => '<p>Ok</p><img src=x onerror=alert(1)><script>bad()</script>']],
            ],
        ]);

        $response = $this->getJson('/api/v1/pages/home')->assertOk();

        $response->assertJsonPath('data.blocks.0.data.image.alt', 'Hero image')
            ->assertJsonPath('data.blocks.1.data.items.0.value', '4')
            ->assertJsonPath('data.blocks.1.data.items.1.value', '3')
            ->assertJsonPath('data.blocks.2.data.books.0.title', 'Featured Guide')
            ->assertJsonPath('data.blocks.3.data.services.0.title', 'Business Formation');

        $html = $response->json('data.blocks.4.data.body_html');
        $this->assertSame('<p>Ok</p>', $html);
    }

    public function test_video_hero_slider_exposes_video_and_poster(): void
    {
        $video = Media::factory()->create(['path' => 'media/hero/clip.mp4', 'mime_type' => 'video/mp4', 'width' => null, 'height' => null]);
        $poster = Media::factory()->create(['alt' => 'Poster']);
        Book::factory()->published()->count(2)->create();

        Page::factory()->create([
            'slug' => 'home',
            'blocks' => [['type' => 'hero_slider', 'data' => ['interval' => 7, 'slides' => [
                ['heading' => '{{products.count}} *startup* blueprints', 'video_id' => $video->id, 'image_id' => $poster->id, 'link_label' => 'Browse', 'link_url' => '/products'],
            ]]]],
        ]);

        $this->getJson('/api/v1/pages/home')
            ->assertOk()
            ->assertJsonPath('data.blocks.0.type', 'hero_slider')
            ->assertJsonPath('data.blocks.0.data.slides.0.heading', '2 *startup* blueprints')
            ->assertJsonPath('data.blocks.0.data.slides.0.video.mime_type', 'video/mp4')
            ->assertJsonPath('data.blocks.0.data.slides.0.image.alt', 'Poster');
    }

    public function test_draft_page_is_not_found(): void
    {
        $page = Page::factory()->draft()->create();

        $this->getJson("/api/v1/pages/{$page->slug}")->assertNotFound();
    }

    public function test_site_endpoint_exposes_public_settings_and_navigation_only(): void
    {
        SiteSetting::putMany(['contact.email' => 'hello@example.com', 'contact.notification_email' => 'private-inbox@example.com']);

        $response = $this->getJson('/api/v1/site')->assertOk();

        $response->assertJsonPath('data.contact.email', 'hello@example.com')
            ->assertJsonStructure(['data' => ['name', 'contact', 'seo', 'navigation' => ['header', 'footer', 'legal']]]);
        $this->assertStringNotContainsString('private-inbox@example.com', $response->getContent());
    }

    public function test_api_responses_carry_security_headers(): void
    {
        $this->getJson('/api/v1/site')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
