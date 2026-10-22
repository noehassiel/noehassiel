<?php

namespace App\Livewire\Quote;

use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The client-facing proposal page: a secret link plus a password.
 */
#[Layout('components.layouts.quote')]
class Show extends Component
{
    private const MAX_ATTEMPTS = 5;

    #[Locked]
    public string $token = '';

    public string $password = '';

    public function mount(string $token): void
    {
        abort_unless(Proposal::findByPublicToken($token), 404);

        $this->token = $token;
    }

    public function unlock(): void
    {
        $proposal = $this->proposal();
        $limiterKey = "quote-unlock:{$proposal->id}:".request()->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($limiterKey) / 60);
            $this->addError('password', "Demasiados intentos. Intenta de nuevo en {$minutes} min.");

            return;
        }

        if (! $proposal->checkAccessPassword($this->password)) {
            RateLimiter::hit($limiterKey, 600);
            $this->addError('password', 'La contraseña no es correcta.');

            return;
        }

        RateLimiter::clear($limiterKey);
        session()->put($this->sessionKey($proposal), true);
        $this->password = '';

        // Only count the client opening it, not the admin previewing it
        if (! Gate::allows('access-admin')) {
            $proposal->recordView();
        }
    }

    public function downloadPdf(): StreamedResponse
    {
        $proposal = $this->proposal();
        abort_unless($this->isUnlocked($proposal), 403);

        $pdf = Pdf::loadView('pdf.proposal', ['proposal' => $proposal->load(['client', 'lead', 'stages', 'items'])])
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            "propuesta-{$proposal->number}.pdf"
        );
    }

    /** Re-resolved on every request so disabling or rotating the link takes effect immediately. */
    private function proposal(): Proposal
    {
        $proposal = Proposal::findByPublicToken($this->token);
        abort_unless($proposal, 404);

        return $proposal;
    }

    /** Tied to the token, so rotating the link also locks out existing sessions. */
    private function sessionKey(Proposal $proposal): string
    {
        return "quotes.unlocked.{$proposal->id}.".hash('sha256', $proposal->public_token);
    }

    private function isUnlocked(Proposal $proposal): bool
    {
        return session()->get($this->sessionKey($proposal), false) === true;
    }

    public function render(): View
    {
        $proposal = $this->proposal();
        $isUnlocked = $this->isUnlocked($proposal);

        if ($isUnlocked) {
            $proposal->load(['client', 'lead', 'stages', 'items']);
        }

        return view('livewire.quote.show', [
            'proposal' => $proposal,
            'isUnlocked' => $isUnlocked,
            'isExpired' => $proposal->valid_until?->endOfDay()->isPast() ?? false,
        ])->title($isUnlocked ? "{$proposal->title} — noehassiel" : 'Propuesta — noehassiel');
    }
}
