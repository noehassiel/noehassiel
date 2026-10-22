<div>
    @if (! $isUnlocked)
        {{-- Password gate: shows nothing about the proposal until unlocked --}}
        <div class="mx-auto max-w-sm py-10">
            <p class="portal-eyebrow mb-3">noehassiel / propuesta</p>
            <h1 class="text-3xl font-extrabold tracking-tight">Tu propuesta está lista.</h1>
            <p class="mt-3 text-[var(--silver)]">Escribe la contraseña que te compartimos para verla.</p>

            <form wire:submit="unlock" class="mt-8 flex flex-col gap-3">
                <label for="quote-password" class="portal-eyebrow">Contraseña</label>
                <input id="quote-password" wire:model="password" type="text" required autofocus
                    autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="xxxx-xxxx"
                    @error('password') aria-invalid="true" aria-describedby="quote-password-error" @enderror
                    class="rounded-xl border border-[#2A2A2A] bg-[#111111] px-4 py-3 font-mono text-lg tracking-wider text-[var(--stone)] placeholder:text-[#3A3A3A]">
                @error('password')
                    <p id="quote-password-error" role="alert" class="text-sm text-[var(--ember)]">{{ $message }}</p>
                @enderror

                <button type="submit" wire:loading.attr="disabled"
                    class="mt-2 cursor-pointer rounded-xl bg-[var(--ember)] px-4 py-3 font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90 disabled:opacity-60">
                    Ver propuesta
                </button>
            </form>
        </div>
    @else
        @php
            $money = fn ($amount) => '$'.number_format((float) $amount, 2).' '.$proposal->currency;
            $taxAmount = ($proposal->subtotal - $proposal->discount) * ($proposal->tax_rate / 100);
        @endphp

        <article class="flex flex-col gap-12">
            {{-- Cover --}}
            <header>
                <p class="portal-eyebrow">{{ $proposal->number }} · {{ $proposal->project_type->label() }}</p>
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-balance sm:text-5xl">{{ $proposal->title }}</h1>
                <p class="mt-4 text-lg text-[var(--silver)]">
                    Para {{ $proposal->recipient()?->name }}
                    @if ($proposal->valid_until)
                        <span class="text-[var(--slate)]">· válida hasta el {{ $proposal->valid_until->translatedFormat('j \d\e F, Y') }}</span>
                    @endif
                </p>

                @if ($isExpired)
                    <p role="status" class="portal-card mt-6 px-5 py-4 text-sm text-[var(--silver)]">
                        Esta propuesta ya venció. Escríbeme y la actualizamos.
                    </p>
                @endif
            </header>

            <section class="grid gap-6 sm:grid-cols-2">
                <div class="portal-card p-6">
                    <h2 class="portal-eyebrow mb-3">Qué haremos</h2>
                    <p class="leading-relaxed whitespace-pre-line text-[var(--silver)]">{{ $proposal->what_we_do }}</p>
                </div>
                <div class="portal-card p-6">
                    <h2 class="portal-eyebrow mb-3">Cómo lo haremos</h2>
                    <p class="leading-relaxed whitespace-pre-line text-[var(--silver)]">{{ $proposal->how_we_do_it }}</p>
                </div>
            </section>

            @if ($proposal->includes || $proposal->excludes)
                <section class="grid gap-6 sm:grid-cols-2">
                    @if ($proposal->includes)
                        <div>
                            <h2 class="portal-eyebrow mb-4">Incluye</h2>
                            <ul class="flex flex-col gap-2">
                                @foreach ($proposal->includes as $included)
                                    <li class="flex gap-3"><span aria-hidden="true" class="font-mono text-[var(--ember)]">+</span>{{ $included }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($proposal->excludes)
                        <div>
                            <h2 class="portal-eyebrow mb-4">No incluye</h2>
                            <ul class="flex flex-col gap-2 text-[var(--silver)]">
                                @foreach ($proposal->excludes as $excluded)
                                    <li class="flex gap-3"><span aria-hidden="true" class="font-mono text-[var(--slate)]">−</span>{{ $excluded }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endif

            @if ($proposal->stages->isNotEmpty())
                <section>
                    <h2 class="portal-eyebrow mb-4">Etapas</h2>
                    <ol class="flex flex-col gap-3">
                        @foreach ($proposal->stages as $stage)
                            <li class="portal-card flex gap-5 p-5">
                                <span class="font-mono text-sm text-[var(--ember)]">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                                        <h3 class="font-bold">{{ $stage->name }}</h3>
                                        @if ($stage->duration)
                                            <span class="font-mono text-xs text-[var(--slate)]">{{ $stage->duration }}</span>
                                        @endif
                                    </div>
                                    @if ($stage->description)
                                        <p class="mt-1 text-sm leading-relaxed text-[var(--silver)]">{{ $stage->description }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if ($proposal->items->isNotEmpty())
                <section class="portal-card p-6">
                    <h2 class="portal-eyebrow mb-4">Inversión</h2>
                    <dl class="flex flex-col gap-3">
                        @foreach ($proposal->items as $item)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-[var(--silver)]">
                                    {{ $item->description }}
                                    @if ($item->quantity != 1)
                                        <span class="font-mono text-xs text-[var(--slate)]">× {{ (float) $item->quantity }}</span>
                                    @endif
                                </dt>
                                <dd class="shrink-0 font-mono">{{ $money($item->subtotal) }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <dl class="mt-5 flex flex-col gap-2 border-t border-[#1C1C1C] pt-5 text-sm">
                        <div class="flex justify-between gap-4 text-[var(--silver)]"><dt>Subtotal</dt><dd class="font-mono">{{ $money($proposal->subtotal) }}</dd></div>
                        @if ($proposal->discount > 0)
                            <div class="flex justify-between gap-4 text-[var(--silver)]"><dt>Descuento</dt><dd class="font-mono">− {{ $money($proposal->discount) }}</dd></div>
                        @endif
                        @if ($proposal->tax_rate > 0)
                            <div class="flex justify-between gap-4 text-[var(--silver)]"><dt>IVA ({{ (float) $proposal->tax_rate }}%)</dt><dd class="font-mono">{{ $money($taxAmount) }}</dd></div>
                        @endif
                        <div class="mt-2 flex items-baseline justify-between gap-4">
                            <dt class="font-bold">Total</dt>
                            <dd class="font-mono text-2xl font-bold text-[var(--ember)]">{{ $money($proposal->total) }}</dd>
                        </div>
                    </dl>
                </section>
            @endif

            @if ($proposal->notes)
                <section>
                    <h2 class="portal-eyebrow mb-3">Notas y términos</h2>
                    <p class="text-sm leading-relaxed whitespace-pre-line text-[var(--silver)]">{{ $proposal->notes }}</p>
                </section>
            @endif

            <div class="flex flex-wrap gap-3">
                <button type="button" wire:click="downloadPdf"
                    class="cursor-pointer rounded-xl border border-[#2A2A2A] px-5 py-3 font-mono text-xs uppercase tracking-[0.16em] transition-colors hover:border-[var(--ember)]">
                    Descargar PDF
                </button>
            </div>
        </article>
    @endif
</div>
