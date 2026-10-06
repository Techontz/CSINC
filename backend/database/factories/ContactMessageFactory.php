<?php

namespace Database\Factories;

use App\Enums\MessageStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('404-555-####'),
            'subject' => 'Business consulting inquiry',
            'message' => fake()->paragraph(),
            'details' => ['growth_stage' => 'Startup', 'challenges' => ['Strategy Development']],
            'source' => 'contact',
            'status' => MessageStatus::New,
        ];
    }
}
