<?php

namespace Database\Factories;

use App\Enums\ProposalProjectType;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proposal>
 */
class ProposalFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 5000, 120000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $taxRate = 16.00;
        $tax = ($subtotal - $discount) * ($taxRate / 100);
        $total = ($subtotal - $discount) + $tax;

        return [
            'client_id' => Client::factory(),
            'number' => 'PROP-'.now()->year.'-'.str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(4),
            'project_type' => fake()->randomElement(ProposalProjectType::cases())->value,
            'what_we_do' => fake()->paragraph(3),
            'how_we_do_it' => fake()->paragraph(3),
            'includes' => fake()->randomElements([
                'Diseño UI/UX',
                'Desarrollo frontend',
                'Desarrollo backend',
                'Integraciones API',
                'SEO técnico',
                'Hosting primer año',
                'Capacitación al cliente',
                'Soporte 30 días post-entrega',
            ], 4),
            'excludes' => fake()->randomElements([
                'Creación de contenido',
                'Fotografía profesional',
                'Dominio web',
                'Redacción de textos',
            ], 2),
            'notes' => fake()->paragraph(2),
            'status' => fake()->randomElement([ProposalStatus::Draft, ProposalStatus::Sent, ProposalStatus::ChangesRequested, ProposalStatus::Rejected, ProposalStatus::Expired])->value,
            'currency' => 'MXN',
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_rate' => $taxRate,
            'total' => $total,
            'valid_until' => fake()->dateTimeBetween('now', '+60 days'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => ProposalStatus::Draft->value]);
    }

    public function sent(): static
    {
        return $this->state(['status' => ProposalStatus::Sent->value, 'sent_at' => now()]);
    }

    public function accepted(): static
    {
        return $this->state([
            'status' => ProposalStatus::Accepted->value,
            'client_signature' => fake()->name(),
            'client_signed_at' => now(),
        ]);
    }
}
