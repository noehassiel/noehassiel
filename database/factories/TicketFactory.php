<?php

namespace Database\Factories;

use App\Enums\TicketType;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'type' => fake()->randomElement(TicketType::cases()),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
        ];
    }
}
