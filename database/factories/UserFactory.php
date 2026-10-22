<?php

namespace Database\Factories;

use App\Enums\PortalRole;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has confirmed two-factor authentication.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-one', 'recovery-code-two'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user belongs to a client team and uses the portal.
     */
    public function forClient(?Client $client = null): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $client?->id ?? Client::factory(),
            'portal_role' => PortalRole::Owner,
        ]);
    }

    /**
     * Indicate that the user is the admin allowed through the "access-admin" gate.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => config('app.admin_email'),
        ]);
    }
}
