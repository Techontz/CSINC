<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(2, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(),
            'blocks' => [
                ['type' => 'hero', 'data' => ['variant' => 'text', 'heading' => $title, 'body' => fake()->sentence()]],
                ['type' => 'rich_text', 'data' => ['body_html' => '<p>'.fake()->paragraph().'</p>']],
            ],
            'status' => PageStatus::Published,
            'is_system' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => PageStatus::Draft]);
    }
}
