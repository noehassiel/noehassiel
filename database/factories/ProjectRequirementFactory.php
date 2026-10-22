<?php

namespace Database\Factories;

use App\Enums\RequirementStatus;
use App\Models\Project;
use App\Models\ProjectRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectRequirement>
 */
class ProjectRequirementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->randomElement(['Logo en vector', 'Fotografías de producto', 'Textos de la página de inicio', 'Accesos al dominio']),
            'description' => null,
            'status' => RequirementStatus::Pending,
            'position' => 0,
        ];
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequirementStatus::Received,
        ])->afterMaking(fn ($requirement) => $requirement->received_at = now());
    }
}
