<?php

namespace App\Models;

use App\Enums\ProposalProjectType;
use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'lead_id',
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

    protected $hidden = [
        'public_token',
        'access_password',
    ];

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'excludes' => 'array',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'client_signed_at' => 'datetime',
            'access_password' => 'encrypted',
            'first_viewed_at' => 'datetime',
            'last_viewed_at' => 'datetime',
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** The client or lead this proposal is addressed to. */
    public function recipient(): Client|Lead|null
    {
        return $this->client ?? $this->lead;
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

    /** Readable alphabet for passwords dictated over WhatsApp or the phone: no 0/o, 1/l/i. */
    private const PASSWORD_ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';

    public static function findByPublicToken(string $token): ?self
    {
        return self::query()->where('public_token', $token)->first();
    }

    public function isShared(): bool
    {
        return $this->public_token !== null;
    }

    /**
     * Create (or rotate) the public link and its password.
     * Rotating invalidates the previous link, so anyone who had it loses access.
     */
    public function enableSharing(): void
    {
        $this->forceFill([
            'public_token' => Str::random(40),
            'access_password' => self::generateAccessPassword(),
        ])->save();
    }

    /** Keep the link but issue a new password. */
    public function rotateAccessPassword(): void
    {
        $this->forceFill(['access_password' => self::generateAccessPassword()])->save();
    }

    public function disableSharing(): void
    {
        $this->forceFill(['public_token' => null, 'access_password' => null])->save();
    }

    public function publicUrl(): ?string
    {
        return $this->isShared() ? route('quotes.show', $this->public_token) : null;
    }

    /** Case-insensitive and tolerant of surrounding spaces, since clients type it by hand. */
    public function checkAccessPassword(string $password): bool
    {
        return $this->access_password !== null
            && hash_equals($this->access_password, Str::lower(trim($password)));
    }

    public function recordView(): void
    {
        $this->forceFill([
            'first_viewed_at' => $this->first_viewed_at ?? now(),
            'last_viewed_at' => now(),
            'view_count' => $this->view_count + 1,
        ])->save();
    }

    /** Eight characters as "xxxx-xxxx", e.g. "k7m2-p9qa". */
    public static function generateAccessPassword(): string
    {
        $characters = collect(range(1, 8))
            ->map(fn () => self::PASSWORD_ALPHABET[random_int(0, strlen(self::PASSWORD_ALPHABET) - 1)])
            ->implode('');

        return substr($characters, 0, 4).'-'.substr($characters, 4);
    }
}
