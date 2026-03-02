<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 20000);
        $tax = round($subtotal * 0.16, 2);

        return [
            'number' => 'F-'.fake()->unique()->numerify('###'),
            'status' => 'sent',
            'issue_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'notes' => fake()->sentence(4),
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'currency' => 'MXN',
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'zip_path' => null,
            'sent_at' => null,
        ];
    }
}
