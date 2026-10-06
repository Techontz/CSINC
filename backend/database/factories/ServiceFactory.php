<?php

namespace Database\Factories;

use App\Enums\ServiceGroup;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
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
            'group' => ServiceGroup::Business,
            'summary' => fake()->sentence(14),
            'highlights' => fake()->words(3),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_published' => true,
        ];
    }
}
