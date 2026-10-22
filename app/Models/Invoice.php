<?php

namespace App\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    /**
     * The disk that holds invoice ZIPs (R2 in production, see filesystems.invoices_disk).
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.invoices_disk'));
    }

    /**
     * Name of the configured invoices disk, for APIs that take a disk name.
     */
    public static function diskName(): string
    {
        return config('filesystems.invoices_disk');
    }

    /**
     * Whether the invoice's ZIP is actually present on the invoices disk.
     */
    public function hasZip(): bool
    {
        return $this->zip_path && static::disk()->exists($this->zip_path);
    }
}
