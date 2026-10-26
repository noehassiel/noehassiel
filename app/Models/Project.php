<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'progress_note',
        'starts_on',
        'due_on',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'starts_on' => 'date',
            'due_on' => 'date',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** The proposal this project came from, when it was sold through one. */
    public function proposal(): HasOne
    {
        return $this->hasOne(Proposal::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->latest();
    }

    public function links(): HasMany
    {
        return $this->hasMany(ProjectLink::class)->oldest();
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Requirements the client still owes (not yet received by the admin).
     */
    public function outstandingRequirements(): HasMany
    {
        return $this->requirements()->where('status', '!=', RequirementStatus::Received);
    }

    /**
     * Projects a portal user may open: their client's, narrowed to the selected ones for restricted members.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->where('client_id', $user->client_id);

        if (! $user->seesAllProjects()) {
            $query->whereHas('members', fn (Builder $members) => $members->whereKey($user->id));
        }
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Projects the client still sees as ongoing work.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', ProjectStatus::Delivered);
    }
}
