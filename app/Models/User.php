<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PortalRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    use HasRoles;

    /**
     * Mirrors the column default so new, unsaved users already carry a language.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'locale' => 'es',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'locale',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'portal_role' => PortalRole::class,
            'can_view_all_projects' => 'boolean',
            'can_view_invoices' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Whether this account belongs to a client team (and so uses the portal, not the admin).
     */
    public function isClient(): bool
    {
        return $this->client_id !== null;
    }

    /**
     * Projects a member was given access to; owners and "all projects" members don't need rows here.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function isPortalOwner(): bool
    {
        return $this->isClient() && $this->portal_role === PortalRole::Owner;
    }

    public function seesAllProjects(): bool
    {
        return $this->isPortalOwner() || $this->can_view_all_projects;
    }

    public function canSeeProject(Project $project): bool
    {
        return $this->isClient()
            && $this->client_id === $project->client_id
            && ($this->seesAllProjects() || $this->projects()->whereKey($project->id)->exists());
    }

    public function canSeeInvoices(): bool
    {
        return $this->isPortalOwner() || ($this->isClient() && $this->can_view_invoices);
    }
}
