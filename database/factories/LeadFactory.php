<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company' => fake()->optional()->company(),
            'project_type' => fake()->randomElement(Lead::PROJECT_TYPES),
            'budget' => fake()->randomElement(Lead::BUDGETS),
            'timeline' => fake()->randomElement(Lead::TIMELINES),
            'message' => fake()->paragraph(),
            'language' => fake()->randomElement(['en', 'es']),
        ];
    }

    public function spanish(): static
    {
        return $this->state(fn (): array => ['language' => 'es']);
    }
}
