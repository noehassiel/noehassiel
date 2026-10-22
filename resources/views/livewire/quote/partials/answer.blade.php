{{-- The client's decision: accept (with or without invoice), ask for changes, or cancel --}}
<section class="portal-card p-6 sm:p-8" aria-labelledby="answer-title">
    <h2 id="answer-title" class="text-2xl font-extrabold tracking-tight">¿Cómo seguimos?</h2>
    @if ($proposal->isExpired())
        <p class="mt-2 text-[var(--silver)]">Esta propuesta venció el {{ $proposal->valid_until->translatedFormat('j \d\e F') }}, así que ya no se puede aceptar tal cual. Pídeme una actualización y te mando precios y fechas vigentes.</p>
    @else
        <p class="mt-2 text-[var(--silver)]">Si todo te late, acéptala y te digo cómo pagar el anticipo. Si algo no cuadra, pídeme cambios y la ajusto.</p>
    @endif

    @if ($proposal->canBeAccepted())
    <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-xl border border-[#2A2A2A] p-4 transition-colors has-[:checked]:border-[var(--ember)]">
        <input type="checkbox" wire:model.live="requiresInvoice" class="mt-1 size-4 accent-[var(--ember)]">
        <span>
            <span class="font-bold">Requiero factura</span>
            <span class="mt-0.5 block text-sm text-[var(--silver)]">
                @if ((float) $proposal->tax_rate > 0)
                    El IVA ya está incluido en el total.
                @else
                    Se suma el IVA ({{ \App\Models\Proposal::INVOICE_TAX_RATE }}%). Total con factura: <span class="font-mono">{{ $money($proposal->totalsFor(true)['total']) }}</span>
                @endif
            </span>
        </span>
    </label>
    @endif

    @if ($answering === 'changes')
        <form wire:submit="requestChanges" class="mt-6 flex flex-col gap-3">
            <label for="changes" class="portal-eyebrow">¿Qué te gustaría cambiar?</label>
            <textarea id="changes" wire:model="response" rows="5" required autofocus
                placeholder="Ej. Quisiera quitar el blog y ver si podemos arrancar en enero."
                @error('response') aria-invalid="true" aria-describedby="changes-error" @enderror
                class="rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 text-[var(--stone)] placeholder:text-[#3A3A3A]"></textarea>
            @error('response')
                <p id="changes-error" role="alert" class="text-sm text-[var(--ember)]">{{ $message }}</p>
            @enderror
            <div class="flex flex-wrap gap-3">
                <button type="submit" wire:loading.attr="disabled" class="cursor-pointer rounded-xl bg-[var(--stone)] px-5 py-3 font-bold text-[var(--obsidian)] disabled:opacity-60">Enviar cambios</button>
                <button type="button" wire:click="$set('answering', null)" class="cursor-pointer px-3 py-3 text-sm text-[var(--silver)] hover:text-[var(--stone)]">Volver</button>
            </div>
        </form>
    @elseif ($answering === 'reject')
        <form wire:submit="reject" class="mt-6 flex flex-col gap-3">
            <label for="reason" class="portal-eyebrow">¿Nos cuentas por qué? (opcional)</label>
            <textarea id="reason" wire:model="response" rows="3"
                class="rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 text-[var(--stone)]"></textarea>
            <div class="flex flex-wrap gap-3">
                <button type="submit" wire:loading.attr="disabled" class="cursor-pointer rounded-xl border border-[#2A2A2A] px-5 py-3 font-bold disabled:opacity-60">Sí, cancelar propuesta</button>
                <button type="button" wire:click="$set('answering', null)" class="cursor-pointer px-3 py-3 text-sm text-[var(--silver)] hover:text-[var(--stone)]">Volver</button>
            </div>
        </form>
    @else
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            @if ($proposal->canBeAccepted())
                <button type="button" wire:click="accept" wire:loading.attr="disabled"
                    class="cursor-pointer rounded-xl bg-[var(--ember)] px-6 py-3 font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90 disabled:opacity-60">
                    Aceptar propuesta
                </button>
            @endif
            <button type="button" wire:click="$set('answering', 'changes')"
                class="cursor-pointer rounded-xl border border-[#2A2A2A] px-6 py-3 font-bold transition-colors hover:border-[var(--stone)]">
                {{ match (true) {
                    $proposal->status === \App\Enums\ProposalStatus::ChangesRequested => 'Enviar más comentarios',
                    $proposal->isExpired() => 'Pedir actualización',
                    default => 'Pedir cambios',
                } }}
            </button>
            <button type="button" wire:click="$set('answering', 'reject')"
                class="cursor-pointer px-4 py-3 text-sm text-[var(--silver)] transition-colors hover:text-[var(--stone)] sm:ml-auto">
                Cancelar
            </button>
        </div>
    @endif
</section>
