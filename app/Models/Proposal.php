<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\ProjectStatus;
use App\Enums\ProposalEventType;
use App\Enums\ProposalProjectType;
use App\Enums\ProposalStatus;
use App\Mail\ProjectWelcomeMail;
use App\Mail\ProposalResponseMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;

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
            'requires_invoice' => 'boolean',
            'responded_at' => 'datetime',
            'payment_method' => PaymentMethod::class,
            'deposit_paid_amount' => 'decimal:2',
            'deposit_paid_at' => 'datetime',
            'balance_payment_method' => PaymentMethod::class,
            'balance_paid_amount' => 'decimal:2',
            'balance_paid_at' => 'datetime',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** The visible back-and-forth: client requests and admin updates, newest first. */
    public function events(): HasMany
    {
        return $this->hasMany(ProposalEvent::class)->latest()->latest('id');
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

    /** noehassiel's standard terms: half before starting, the rest on full delivery. */
    public const DEPOSIT_PERCENT = 50;

    /**
     * The payment schedule for the stored total, or for a total being previewed. The balance absorbs rounding so both add up exactly.
     *
     * @return array<int, array{label: string, description: string, percent: int, amount: float, paid_at: Carbon|null}>
     */
    public function paymentSchedule(?float $total = null): array
    {
        $total ??= (float) $this->total;
        $deposit = round($total * self::DEPOSIT_PERCENT / 100, 2);

        return [
            ['label' => 'Anticipo', 'description' => 'Antes de iniciar el proyecto', 'percent' => self::DEPOSIT_PERCENT, 'amount' => $deposit, 'paid_at' => $this->deposit_paid_at],
            ['label' => 'Liquidación', 'description' => 'Al entregar el proyecto completo', 'percent' => 100 - self::DEPOSIT_PERCENT, 'amount' => round($total - $deposit, 2), 'paid_at' => $this->balance_paid_at],
        ];
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

    /** IVA added when the client asks for an invoice on a proposal quoted without tax. */
    public const INVOICE_TAX_RATE = 16;

    /** The client hasn't closed it yet: they can still ask for changes or cancel. */
    public function isAwaitingAnswer(): bool
    {
        return in_array($this->status, [ProposalStatus::Draft, ProposalStatus::Sent, ProposalStatus::ChangesRequested], true);
    }

    public function isExpired(): bool
    {
        return $this->valid_until?->endOfDay()->isPast() ?? false;
    }

    /** Prices are only honored until the proposal expires; after that the client can only ask for an update. */
    public function canBeAccepted(): bool
    {
        return $this->isAwaitingAnswer() && ! $this->isExpired();
    }

    public function isDepositPaid(): bool
    {
        return $this->deposit_paid_at !== null;
    }

    /** Tax rate that applies if the client does (or doesn't) need an invoice. Tax set by the admin always stays. */
    public function taxRateFor(bool $requiresInvoice): float
    {
        if ((float) $this->tax_rate > 0) {
            return (float) $this->tax_rate;
        }

        return $requiresInvoice ? self::INVOICE_TAX_RATE : 0;
    }

    /** @return array{base: float, tax_rate: float, tax: float, total: float} */
    public function totalsFor(bool $requiresInvoice): array
    {
        $base = max(0, (float) $this->subtotal - (float) $this->discount);
        $taxRate = $this->taxRateFor($requiresInvoice);
        $tax = round($base * $taxRate / 100, 2);

        return ['base' => $base, 'tax_rate' => $taxRate, 'tax' => $tax, 'total' => round($base + $tax, 2)];
    }

    public function accept(bool $requiresInvoice): void
    {
        $totals = $this->totalsFor($requiresInvoice);

        $this->forceFill([
            'status' => ProposalStatus::Accepted,
            'requires_invoice' => $requiresInvoice,
            'tax_rate' => $totals['tax_rate'],
            'total' => $totals['total'],
            'client_response' => null,
            'responded_at' => now(),
        ])->save();

        $this->events()->create([
            'type' => ProposalEventType::Accepted,
            'body' => $requiresInvoice ? 'Con factura.' : null,
        ]);
        $this->notifyAdmin('accepted');
    }

    public function requestChanges(string $changes): void
    {
        $this->forceFill([
            'status' => ProposalStatus::ChangesRequested,
            'client_response' => $changes,
            'responded_at' => now(),
        ])->save();

        $this->events()->create(['type' => ProposalEventType::ChangesRequested, 'body' => $changes]);
        $this->notifyAdmin('changes_requested');
    }

    public function reject(?string $reason): void
    {
        $this->forceFill([
            'status' => ProposalStatus::Rejected,
            'client_response' => $reason ?: null,
            'responded_at' => now(),
        ])->save();

        $this->events()->create(['type' => ProposalEventType::Rejected, 'body' => $reason ?: null]);
        $this->notifyAdmin('rejected');
    }

    /**
     * The admin saved a new version for the client to see. Answering a change request puts it back in their hands.
     */
    public function publishUpdate(?string $note): void
    {
        $this->events()->create(['type' => ProposalEventType::Updated, 'body' => $note ?: null]);

        if ($this->status === ProposalStatus::ChangesRequested) {
            $this->forceFill(['status' => ProposalStatus::Sent, 'client_response' => null])->save();
        }
    }

    /** The client picked how to pay the deposit; transfer and cash then wait for the admin to confirm. */
    public function choosePaymentMethod(PaymentMethod $method): void
    {
        $this->forceFill(['payment_method' => $method])->save();

        if ($method !== PaymentMethod::Card) {
            $this->notifyAdmin('payment_method_chosen');
        }
    }

    public function depositAmount(): float
    {
        return $this->paymentSchedule()[0]['amount'];
    }

    /**
     * Gross up an amount so that after Stripe's fee (and the IVA on that fee) we still receive it in full.
     *
     * @return array{amount: float, fee: float, charge: float}
     */
    public static function cardCharge(float $amount): array
    {
        $taxFactor = 1 + config('services.stripe_fees.tax_percent') / 100;
        $percent = config('services.stripe_fees.percent') / 100 * $taxFactor;
        $fixed = config('services.stripe_fees.fixed') * $taxFactor;

        $charge = ceil(($amount + $fixed) / (1 - $percent) * 100) / 100;

        return ['amount' => $amount, 'fee' => round($charge - $amount, 2), 'charge' => $charge];
    }

    /** Meses sin intereses: Stripe only offers them on MXN card charges, and we only from a minimum amount. */
    public static function allowsInstallments(float $charge, string $currency): bool
    {
        return strtoupper($currency) === 'MXN' && $charge >= config('services.stripe_installments.min_charge');
    }

    public function balanceAmount(): float
    {
        return $this->paymentSchedule()[1]['amount'];
    }

    /** The final payment, confirmed by the admin once the project is delivered. */
    public function markBalancePaid(PaymentMethod $method, float $amount): void
    {
        if ($this->balance_paid_at || ! $this->isDepositPaid()) {
            return;
        }

        $this->forceFill([
            'balance_payment_method' => $method,
            'balance_paid_amount' => $amount,
            'balance_paid_at' => now(),
        ])->save();
    }

    /**
     * Ask Stripe whether this proposal's checkout was paid and, if so, record the deposit.
     * Used both by the webhook and by the page the client returns to, so either one is enough.
     */
    public function syncStripeCheckout(): bool
    {
        if ($this->isDepositPaid()) {
            return true;
        }

        if (! $this->stripe_checkout_session_id) {
            return false;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($this->stripe_checkout_session_id);

        if ($session->payment_status !== 'paid' || (string) ($session->metadata['proposal_id'] ?? '') !== (string) $this->id) {
            return false;
        }

        $this->markDepositPaid(PaymentMethod::Card, $session->amount_total / 100);

        return true;
    }

    /**
     * Record the deposit and turn the proposal into a project. Safe to call twice (webhook retries, double clicks).
     */
    public function markDepositPaid(PaymentMethod $method, float $amount): Project
    {
        $project = DB::transaction(function () use ($method, $amount): Project {
            $proposal = self::query()->lockForUpdate()->findOrFail($this->id);

            if ($proposal->project_id) {
                return $proposal->project;
            }

            $client = $proposal->client ?? $proposal->lead->convertToClient();

            $project = Project::create([
                'client_id' => $client->id,
                'name' => $proposal->title,
                'description' => $proposal->what_we_do,
                'status' => ProjectStatus::Brief,
                'starts_on' => today(),
            ]);

            $proposal->forceFill([
                'client_id' => $client->id,
                'project_id' => $project->id,
                'status' => ProposalStatus::Paid,
                'payment_method' => $method,
                'deposit_paid_amount' => $amount,
                'deposit_paid_at' => now(),
            ])->save();

            $this->setRawAttributes($proposal->getAttributes(), true);
            $this->setRelation('project', $project);

            return $project;
        });

        if ($project->wasRecentlyCreated) {
            $this->notifyAdmin('paid');
            $this->sendProjectWelcome($project);
        }

        return $project;
    }

    /**
     * Welcome the client to their new project, with a portal invitation if nobody on their team has an account yet.
     */
    private function sendProjectWelcome(Project $project): void
    {
        $client = $project->client;
        $email = $client->email ?? $this->lead?->email;

        if (! $email) {
            return;
        }

        $invitationToken = null;

        if (! $client->users()->exists()) {
            $locale = $this->lead?->language === 'en' ? 'en' : 'es';
            $client->invitations()->pending()->where('email', Str::lower($email))->delete();
            [, $invitationToken] = ClientInvitation::issue($client, $email, $locale);
        }

        Mail::to($email)->queue(new ProjectWelcomeMail($project, $invitationToken));
    }

    private function notifyAdmin(string $event): void
    {
        Mail::to(config('services.proposals.notify_email'))->queue(new ProposalResponseMail($this, $event));
    }
}
