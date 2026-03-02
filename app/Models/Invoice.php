<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'number',
        'status',
        'issue_date',
        'due_date',
        'total',
        'tax',
        'discount',
        'notes',
        'currency',
        'payment_method',
        'payment_status',
        'zip_path',
        'sent_at',
    ];

    public function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'sent_at' => 'datetime',
            'total' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
