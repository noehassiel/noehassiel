<ul class="portal-card divide-y divide-[#1C1C1C]">
    @foreach ($invoices as $invoice)
        <li wire:key="invoice-{{ $invoice->id }}" class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
            <div class="min-w-0">
                <p class="font-mono text-xs text-[var(--ember)]">{{ $invoice->number }}</p>
                @if ($invoice->notes)
                    <p class="mt-1 truncate text-sm text-[var(--silver)]">{{ $invoice->notes }}</p>
                @endif
                <p class="mt-1 font-mono text-xs text-[var(--slate)]">{{ __('portal.invoices.issued', ['date' => $invoice->issue_date->translatedFormat('j M Y')]) }}</p>
            </div>

            <div class="flex items-center gap-5">
                <p class="font-mono text-sm font-semibold">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</p>

                @if ($invoice->zip_path)
                    <a href="{{ route('portal.invoices.download', $invoice) }}"
                        class="font-mono text-xs uppercase tracking-[0.14em] text-[var(--stone)] underline decoration-[var(--ember)] underline-offset-4 hover:text-[var(--ember)]">
                        ↓ {{ __('portal.invoices.download') }}
                    </a>
                @else
                    <span class="font-mono text-xs text-[var(--slate)]">{{ __('portal.invoices.unavailable') }}</span>
                @endif
            </div>
        </li>
    @endforeach
</ul>
