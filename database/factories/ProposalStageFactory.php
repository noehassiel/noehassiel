<?php

namespace Database\Factories;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProposalStage>
 */
class ProposalStageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'name' => fake()->randomElement([
                'Descubrimiento y estrategia',
                'Diseño UI/UX',
                'Desarrollo frontend',
                'Desarrollo backend',
                'Pruebas y QA',
                'Lanzamiento',
                'Soporte post-lanzamiento',
            ]),
            'description' => fake()->sentence(),
            'duration' => fake()->randomElement(['1 semana', '2 semanas', '3 semanas', '1 mes']),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
