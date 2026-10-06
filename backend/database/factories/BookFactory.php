<?php

namespace Database\Factories;

use App\Enums\BookStatus;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true)).' Startup';

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(10),
            'description' => '<p>'.fake()->paragraph().'</p>',
            'sku' => strtoupper(fake()->unique()->bothify('GB-###??')),
            'price_cents' => fake()->randomElement([4700, 9700, 14700, 19700]),
            'currency' => 'USD',
            'language' => 'English',
            'format' => 'PDF download',
            'status' => BookStatus::Draft,
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => BookStatus::Published, 'published_at' => now()->subDay()]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function archived(): static
    {
        return $this->state(['status' => BookStatus::Archived]);
    }

    /**
     * Attaches a stored PDF so the product can be bought and downloaded.
     */
    public function withFile(): static
    {
        return $this->state(function (): array {
            $path = 'books/files/'.Str::uuid().'.pdf';
            Storage::disk(Book::PRIVATE_DISK)->put($path, "%PDF-1.4\n%test\n");

            return ['file_path' => $path, 'file_original_name' => 'guide.pdf'];
        });
    }
}
