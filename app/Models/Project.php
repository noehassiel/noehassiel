<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Projects the client still sees as ongoing work.
     *
     * @param  Builder<Project>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', ProjectStatus::Delivered);
    }
}
