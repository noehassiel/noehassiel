<div class="space-y-10">
    <h1 class="text-4xl font-black tracking-tight sm:text-5xl">{{ __('portal.invoices.title') }}</h1>

    @if ($invoices->isEmpty())
        <p class="text-[var(--silver)]">{{ __('portal.invoices.empty') }}</p>
    @else
        @include('livewire.portal.partials.invoice-list', ['invoices' => $invoices])
    @endif
</div>
