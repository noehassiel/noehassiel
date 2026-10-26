<?php

namespace App\Models;

use App\Enums\RequirementStatus;
use Database\Factories\ProjectRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRequirement extends Model
{
    /** @use HasFactory<ProjectRequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'due_on',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequirementStatus::class,
            'due_on' => 'date',
            'client_marked_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Row-by-row so each file also leaves storage; the FK cascade alone would orphan them
        static::deleting(fn (ProjectRequirement $requirement) => $requirement->files->each->delete());
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Files the client uploaded here instead of sharing a folder.
     */
    public function files(): HasMany
    {
        return $this->hasMany(RequirementFile::class)->latest()->latest('id');
    }

    /**
     * The client says they uploaded it to their shared folder; the admin still has to check.
     */
    public function markUploadedByClient(): void
    {
        if ($this->status === RequirementStatus::Received) {
            return;
        }

        $this->forceFill([
            'status' => RequirementStatus::ClientMarked,
            'client_marked_at' => now(),
        ])->save();
    }

    /**
     * The admin confirms the material arrived.
     */
    public function markReceived(): void
    {
        $this->forceFill([
            'status' => RequirementStatus::Received,
            'received_at' => now(),
        ])->save();
    }

    /**
     * The admin checked and something is still missing; it goes back to the client.
     */
    public function markPending(): void
    {
        $this->forceFill([
            'status' => RequirementStatus::Pending,
            'client_marked_at' => null,
            'received_at' => null,
        ])->save();
    }
}
