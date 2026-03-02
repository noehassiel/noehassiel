<?php

namespace App\Models;

use App\Enums\ProposalProjectType;
use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'number',
        'title',
        'project_type',
        'what_we_do',
        'how_we_do_it',
        'includes',
        'excludes',
        'notes',
        'status',
        'currency',
        'subtotal',
        'discount',
        'tax_rate',
        'total',
        'valid_until',
        'pdf_path',
        'sent_at',
        'client_signature',
        'client_signed_at',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'excludes' => 'array',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'client_signed_at' => 'datetime',
            'status' => ProposalStatus::class,
            'project_type' => ProposalProjectType::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProposalStage::class)->orderBy('order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class)->orderBy('order');
    }

    /** Generate the next sequential proposal number. */
    public static function generateNumber(): string
    {
        $year = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;

        return sprintf('PROP-%d-%03d', $year, $count);
    }

    /** Recalculate subtotal and total from items. */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $tax = $subtotal * ($this->tax_rate / 100);
        $total = ($subtotal - $this->discount) + $tax;

        $this->update([
            'subtotal' => $subtotal,
            'total' => max(0, $total),
        ]);
    }
}
