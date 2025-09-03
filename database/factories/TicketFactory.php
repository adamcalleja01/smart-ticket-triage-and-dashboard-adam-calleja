<?php

namespace Database\Factories;

use App\Enums\TicketStatus;
use Symfony\Component\Uid\Ulid;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Ulid::generate(),
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(TicketStatus::values()),
            'category' => $this->faker->word(),
            'explanation' => $this->faker->sentence(),
            'confidence' => $this->faker->randomFloat(2, 0, 1),
        ];
    }
}
