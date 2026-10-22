<?php

namespace Database\Factories;

use App\Models\SpotifyAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpotifyAccount>
 */
class SpotifyAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refresh_token' => fake()->sha256(),
        ];
    }
}
