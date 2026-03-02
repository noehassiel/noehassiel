<?php

namespace Database\Factories;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProposalItem>
 */
class ProposalItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 5);
        $unitPrice = fake()->randomFloat(2, 500, 25000);

        return [
            'proposal_id' => Proposal::factory(),
            'description' => fake()->randomElement([
                'Diseño de interfaz (UI)',
                'Desarrollo de módulo de usuarios',
                'Integración con pasarela de pagos',
                'Optimización SEO técnica',
                'Configuración de servidor',
                'Diseño de marca completo',
                'Animaciones y microinteracciones',
            ]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => round($quantity * $unitPrice, 2),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
