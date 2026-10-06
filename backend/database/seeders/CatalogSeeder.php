<?php

namespace Database\Seeders;

use App\Enums\BookStatus;
use App\Media\MediaUploader;
use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Database\Seeder;

/**
 * Seeds the CSinc91 digital product catalogue from the snapshot taken of the
 * WooCommerce store (database/data/catalog.json), including cover artwork.
 */
class CatalogSeeder extends Seeder
{
    /** Flagship guides highlighted on the homepage; editable in the admin. */
    private const FEATURED = [
        'assisted-living-facility-nationwide-edition',
        'hospice-care-agency-startup',
        'home-health-agency-startup',
        'group-home-startup',
        'residential-rehab-sober-living-startup',
        'staffing-agency-startup',
        'trucking-dispatch-startup',
        'cybersecurity-business-startup',
    ];

    public function __construct(private MediaUploader $uploader) {}

    public function run(): void
    {
        $categories = [
            'healthcare' => BookCategory::query()->updateOrCreate(['slug' => 'healthcare'], [
                'name' => 'Healthcare',
                'headline' => 'Healthcare & Residential Care Startup Guides',
                'description' => 'Assisted living, group homes, veterans housing, hospice, transport & care training — priced low to high.',
                'sort_order' => 1,
                'is_published' => true,
            ]),
            'general-business' => BookCategory::query()->updateOrCreate(['slug' => 'general-business'], [
                'name' => 'General Business',
                'headline' => 'General Business Startup Guides',
                'description' => 'Financial, retail, transport, cleaning, trades, security & service businesses — priced low to high.',
                'sort_order' => 2,
                'is_published' => true,
            ]),
        ];

        $catalog = json_decode((string) file_get_contents(database_path('data/catalog.json')), true);

        foreach ($catalog as $item) {
            $cover = $item['cover_url']
                ? MediaSeeder::import($this->uploader, $item['cover_url'], 'media/covers/'.$item['slug'], 'covers', [
                    'title' => $item['title'].' cover',
                    'alt' => $item['title'].' — startup guide cover',
                ], fn (string $message) => $this->command?->warn($message))
                : null;

            $book = Book::query()->updateOrCreate(['slug' => $item['slug']], [
                'title' => $item['title'],
                'sku' => $item['sku'],
                'short_description' => $item['short_description'],
                'description' => $item['description'],
                'price_cents' => $item['price_cents'],
                'sale_price_cents' => $item['sale_price_cents'],
                'currency' => $item['currency'],
                'author' => 'CSinc91',
                'publisher' => 'CSinc91',
                'language' => 'English',
                'format' => 'PDF download',
                'cover_id' => $cover?->getKey(),
                'status' => BookStatus::Published,
                'published_at' => now(),
                'is_featured' => in_array($item['slug'], self::FEATURED, true),
                'sort_order' => $item['sort_order'],
            ]);

            $book->categories()->sync(collect($item['categories'])->map(fn (string $slug) => $categories[$slug]->getKey()));
        }
    }
}
