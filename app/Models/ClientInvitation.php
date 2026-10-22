<?php

namespace App\Models;

use Database\Factories\ClientInvitationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ClientInvitation extends Model
{
    /** @use HasFactory<ClientInvitationFactory> */
    use HasFactory;

    public const EXPIRES_IN_DAYS = 7;

    protected $fillable = [
        'client_id',
        'email',
        'locale',
        'token_hash',
        'invited_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Create an invitation and return it with the plain token, which is never stored.
     *
     * @return array{0: ClientInvitation, 1: string}
     */
    public static function issue(Client $client, string $email, string $locale = 'es', ?User $inviter = null): array
    {
        $token = Str::random(48);

        $invitation = static::create([
            'client_id' => $client->id,
            'email' => Str::lower($email),
            'locale' => $locale,
            'token_hash' => static::hashToken($token),
            'invited_by' => $inviter?->id,
            'expires_at' => now()->addDays(self::EXPIRES_IN_DAYS),
        ]);

        return [$invitation, $token];
    }

    /**
     * Find a pending, unexpired invitation by its plain token.
     */
    public static function findPendingByToken(string $token): ?self
    {
        return static::query()
            ->pending()
            ->where('token_hash', static::hashToken($token))
            ->first();
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @param  Builder<ClientInvitation>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }
}
