<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const PROJECT_TYPES = ['website', 'web_app', 'ecommerce', 'design', 'other'];

    public const BUDGETS = ['under_30k', '30k_80k', '80k_150k', 'over_150k', 'unsure'];

    public const TIMELINES = ['asap', '1_3_months', '3_6_months', 'flexible'];

    /**
     * Mirrors the column default so a freshly created lead already has a status.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'project_type',
        'budget',
        'timeline',
        'message',
        'language',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'converted_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function isConverted(): bool
    {
        return $this->client_id !== null;
    }

    /**
     * Close the deal: reuse the client with this email or create one, then hand over the lead's proposals.
     */
    public function convertToClient(): Client
    {
        return DB::transaction(function (): Client {
            $client = Client::query()->where('email', $this->email)->first()
                ?? Client::create([
                    'name' => $this->company ?: $this->name,
                    'contact_name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'notes' => Str::limit($this->message, 1000),
                ]);

            // Keep lead_id so the proposal history still shows where the deal came from
            $this->proposals()->whereNull('client_id')->update(['client_id' => $client->id]);

            $this->forceFill([
                'client_id' => $client->id,
                'status' => LeadStatus::Won,
                'converted_at' => now(),
            ])->save();

            return $client;
        });
    }

    /**
     * Human label for one of the landing form options (project type, budget or timeline).
     */
    public function optionLabel(string $field): string
    {
        $group = ['project_type' => 'types', 'budget' => 'budgets', 'timeline' => 'timelines'][$field];

        return __("landing.form.{$group}.{$this->{$field}}", locale: 'es');
    }

    /**
     * WhatsApp chat link for the lead's phone, if it has one.
     */
    public function whatsappUrl(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);

        return $digits ? "https://wa.me/{$digits}" : null;
    }
}
