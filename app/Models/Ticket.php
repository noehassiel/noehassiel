<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\TicketType;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'type',
        'title',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TicketType::class,
            'status' => TicketStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Linear moved the issue: mirror it here so the client sees progress. */
    public function syncLinearState(string $stateType): void
    {
        $status = TicketStatus::fromLinearStateType($stateType);

        $this->forceFill([
            'status' => $status,
            'resolved_at' => $status === TicketStatus::Resolved ? ($this->resolved_at ?? now()) : null,
        ])->save();
    }
}
