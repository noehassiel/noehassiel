<?php

namespace App\Models;

use App\Enums\ProposalEventType;
use Database\Factories\ProposalEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalEvent extends Model
{
    /** @use HasFactory<ProposalEventFactory> */
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'type',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProposalEventType::class,
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }
}
