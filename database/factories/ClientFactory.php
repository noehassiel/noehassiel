<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'contact_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'email' => fake()->companyEmail(),
            'rfc' => strtoupper(fake()->bothify('???######???')),
            'website' => fake()->url(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
