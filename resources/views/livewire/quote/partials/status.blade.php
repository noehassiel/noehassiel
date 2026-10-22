{{-- What happened after the client answered, shown right under the cover --}}
@switch($proposal->status)
    @case(\App\Enums\ProposalStatus::ChangesRequested)
        @include('livewire.quote.partials.changes-received')
        @break

    @case(\App\Enums\ProposalStatus::Sent)
    @case(\App\Enums\ProposalStatus::Draft)
        @php($latest = $proposal->events->first())
        @if ($latest?->type === \App\Enums\ProposalEventType::Updated)
            <div role="status" class="portal-card border-[var(--ember)] p-5">
                <p class="flex flex-wrap items-baseline justify-between gap-x-4">
                    <span class="font-bold">Actualicé tu propuesta.</span>
                    <time datetime="{{ $latest->created_at->toIso8601String() }}" class="font-mono text-xs text-[var(--slate)]">{{ $latest->created_at->translatedFormat('j \d\e F, H:i') }}</time>
                </p>
                <p class="mt-1 whitespace-pre-line text-sm text-[var(--silver)]">{{ $latest->body ?: 'Revísala y dime qué te parece.' }}</p>
            </div>
        @endif
        @break

    @case(\App\Enums\ProposalStatus::Rejected)
        <div role="status" class="portal-card p-5">
            <p class="font-bold">Cancelaste esta propuesta.</p>
            <p class="mt-1 text-sm text-[var(--silver)]">Gracias por considerarnos. Si cambias de opinión o quieres otra idea, escríbeme.</p>
        </div>
        @break

    @case(\App\Enums\ProposalStatus::Paid)
        <div role="status" class="portal-card border-[var(--ember)] p-6 sm:p-8">
            <p class="portal-eyebrow text-[var(--ember)]">Anticipo recibido</p>
            <p class="mt-2 text-3xl font-extrabold tracking-tight">Arrancamos.</p>
            <p class="mt-2 text-[var(--silver)]">Tu proyecto ya está creado. Te mandé un correo para entrar a tu portal, donde vas a ver cada avance.</p>
        </div>
        @break

    @case(\App\Enums\ProposalStatus::Accepted)
        <a href="#pago" class="portal-card flex flex-wrap items-center justify-between gap-3 p-5 transition-colors">
            <span><span class="font-bold">Propuesta aceptada.</span> <span class="text-[var(--silver)]">Falta el anticipo para arrancar.</span></span>
            <span class="font-mono text-xs uppercase tracking-[0.16em] text-[var(--ember)]">Ver formas de pago ↓</span>
        </a>
        @break
@endswitch
