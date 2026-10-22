<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClientInvitation>
 */
class ClientInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'email' => fake()->unique()->safeEmail(),
            'token_hash' => ClientInvitation::hashToken(Str::random(48)),
            'expires_at' => now()->addDays(ClientInvitation::EXPIRES_IN_DAYS),
        ];
    }
}
