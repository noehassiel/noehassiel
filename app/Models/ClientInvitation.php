<?php

namespace App\Models;

use App\Enums\PortalRole;
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
        'portal_role',
        'can_view_all_projects',
        'can_view_invoices',
        'project_ids',
        'token_hash',
        'invited_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'portal_role' => PortalRole::class,
            'can_view_all_projects' => 'boolean',
            'can_view_invoices' => 'boolean',
            'project_ids' => 'array',
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
     * Without $access the invitee becomes an owner (admin invitations, project welcome).
     *
     * @param  array{portal_role?: PortalRole, can_view_all_projects?: bool, can_view_invoices?: bool, project_ids?: array<int, int>}  $access
     * @return array{0: ClientInvitation, 1: string}
     */
    public static function issue(Client $client, string $email, string $locale = 'es', ?User $inviter = null, array $access = []): array
    {
        $token = Str::random(48);

        $invitation = static::create([
            ...$access,
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

    /**
     * Give the new user the role and access this invitation was created with.
     */
    public function grantAccessTo(User $user): void
    {
        $user->forceFill([
            'portal_role' => $this->portal_role,
            'can_view_all_projects' => $this->can_view_all_projects,
            'can_view_invoices' => $this->can_view_invoices,
        ])->save();

        if (! $this->can_view_all_projects && $this->project_ids) {
            $user->projects()->sync($this->client->projects()->whereKey($this->project_ids)->pluck('id'));
        }
    }
}
