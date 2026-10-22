{{-- After accepting: how to pay the deposit. Lives at the bottom, where the client just clicked accept --}}
@if ($paymentReturn === 'listo' && $proposal->payment_method === \App\Enums\PaymentMethod::Card)
    <div role="status" wire:poll.4s class="portal-card p-6">
        <p class="font-bold">Confirmando tu pago…</p>
        <p class="mt-1 text-sm text-[var(--silver)]">Stripe nos avisa en unos segundos. No cierres esta página.</p>
    </div>
@else
    <section class="portal-card border-[var(--ember)] p-6 sm:p-8" aria-labelledby="accepted-title">
        <p class="portal-eyebrow text-[var(--ember)]">Propuesta aceptada</p>
        <h2 id="accepted-title" class="mt-2 text-3xl font-extrabold tracking-tight">¡Excelente! Hagámoslo.</h2>
        <p class="mt-2 text-[var(--silver)]">
            Para arrancar solo falta el anticipo del 50%:
            <span class="font-mono text-[var(--stone)]">{{ $money($proposal->depositAmount()) }}</span>.
            Elige cómo prefieres pagarlo.
        </p>

        <div class="mt-6 grid gap-3 sm:grid-cols-3" role="radiogroup" aria-label="Forma de pago">
            @foreach (\App\Enums\PaymentMethod::cases() as $method)
                <button type="button" role="radio" aria-checked="{{ $proposal->payment_method === $method ? 'true' : 'false' }}"
                    wire:click="choosePaymentMethod('{{ $method->value }}')"
                    @class([
                        'cursor-pointer rounded-xl border p-4 text-left transition-colors',
                        'border-[var(--ember)]' => $proposal->payment_method === $method,
                        'border-[#2A2A2A] hover:border-[var(--silver)]' => $proposal->payment_method !== $method,
                    ])>
                    <span class="font-bold">{{ $method->label() }}</span>
                    <span class="mt-0.5 block text-xs text-[var(--silver)]">
                        {{ match ($method) {
                            \App\Enums\PaymentMethod::Transfer => 'Sin comisión',
                            \App\Enums\PaymentMethod::Cash => 'Sin comisión, en persona',
                            \App\Enums\PaymentMethod::Card => '+ '.$money($cardCharge['fee']).' de comisión',
                        } }}
                    </span>
                </button>
            @endforeach
        </div>

        @if ($proposal->payment_method === \App\Enums\PaymentMethod::Transfer)
            <dl class="mt-6 grid gap-3 rounded-xl bg-[#0D0D0D] p-5 text-sm sm:grid-cols-2"
                x-data="{ copied: null, copy(value, key) { navigator.clipboard.writeText(value); this.copied = key; setTimeout(() => this.copied = null, 2000) } }">
                <div><dt class="portal-eyebrow">Banco</dt><dd class="mt-1">{{ $bank['name'] ?? '—' }}</dd></div>
                <div><dt class="portal-eyebrow">Beneficiario</dt><dd class="mt-1">{{ $bank['beneficiary'] ?? '—' }}</dd></div>
                <div>
                    <dt class="portal-eyebrow">CLABE</dt>
                    <dd class="mt-1 flex items-center gap-3">
                        <span class="font-mono">{{ $bank['clabe'] ?? '—' }}</span>
                        <button type="button" @click="copy(@js($bank['clabe'] ?? ''), 'clabe')"
                            class="cursor-pointer rounded-lg border border-[#2A2A2A] px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-[var(--silver)] transition-colors hover:border-[var(--ember)] hover:text-[var(--stone)]"
                            :aria-label="copied === 'clabe' ? 'Copiado' : 'Copiar CLABE'"
                            x-text="copied === 'clabe' ? 'Copiado' : 'Copiar'">Copiar</button>
                    </dd>
                </div>
                <div>
                    <dt class="portal-eyebrow">Concepto</dt>
                    <dd class="mt-1 flex items-center gap-3">
                        <span class="font-mono">{{ $proposal->number }}</span>
                        <button type="button" @click="copy(@js($proposal->number), 'concept')"
                            class="cursor-pointer rounded-lg border border-[#2A2A2A] px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-[var(--silver)] transition-colors hover:border-[var(--ember)] hover:text-[var(--stone)]"
                            :aria-label="copied === 'concept' ? 'Copiado' : 'Copiar Concepto'"
                            x-text="copied === 'concept' ? 'Copiado' : 'Copiar'">Copiar</button>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="portal-eyebrow">Monto</dt>
                    <dd class="mt-1 flex items-center gap-3">
                        <span class="font-mono text-lg">{{ $money($proposal->depositAmount()) }}</span>
                        <button type="button" @click="copy(@js(number_format($proposal->depositAmount(), 2, '.', '')), 'amount')"
                            class="cursor-pointer rounded-lg border border-[#2A2A2A] px-2.5 py-1 font-mono text-[10px] uppercase tracking-[0.14em] text-[var(--silver)] transition-colors hover:border-[var(--ember)] hover:text-[var(--stone)]"
                            :aria-label="copied === 'amount' ? 'Copiado' : 'Copiar Monto'"
                            x-text="copied === 'amount' ? 'Copiado' : 'Copiar'">Copiar</button>
                    </dd>
                </div>
            </dl>
            <p class="mt-3 text-sm text-[var(--silver)]">En cuanto vea la transferencia en mi cuenta te confirmo y arrancamos. Si puedes, mándame el comprobante por WhatsApp.</p>
        @elseif ($proposal->payment_method === \App\Enums\PaymentMethod::Cash)
            <p class="mt-6 text-sm text-[var(--silver)]">Perfecto. Te escribo para quedar en día y lugar para entregar el anticipo de <span class="font-mono text-[var(--stone)]">{{ $money($proposal->depositAmount()) }}</span>. Al recibirlo arrancamos.</p>
        @elseif ($proposal->payment_method === \App\Enums\PaymentMethod::Card)
            <dl class="mt-6 flex flex-col gap-2 rounded-xl bg-[#0D0D0D] p-5 text-sm">
                <div class="flex justify-between gap-4 text-[var(--silver)]"><dt>Anticipo 50%</dt><dd class="font-mono">{{ $money($cardCharge['amount']) }}</dd></div>
                <div class="flex justify-between gap-4 text-[var(--silver)]"><dt>Comisión de Stripe (pago con tarjeta)</dt><dd class="font-mono">{{ $money($cardCharge['fee']) }}</dd></div>
                <div class="flex justify-between gap-4 font-bold"><dt>Total a pagar</dt><dd class="font-mono">{{ $money($cardCharge['charge']) }}</dd></div>
            </dl>
            @if ($allowsInstallments)
                <p class="mt-3 text-sm text-[var(--silver)]">Con tarjeta de crédito participante puedes pagarlo a <span class="text-[var(--stone)]">meses sin intereses</span>. Eliges el plazo en la pantalla de pago.</p>
            @endif
            <button type="button" wire:click="payWithCard" wire:loading.attr="disabled"
                class="mt-4 w-full cursor-pointer rounded-xl bg-[var(--ember)] px-6 py-3 font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90 disabled:opacity-60 sm:w-auto">
                <span wire:loading.remove wire:target="payWithCard">Pagar con tarjeta</span>
                <span wire:loading wire:target="payWithCard">Abriendo pago seguro…</span>
            </button>
        @endif
    </section>
@endif
