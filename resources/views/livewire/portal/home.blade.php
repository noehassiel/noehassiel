<div class="space-y-14">
    <div>
        <p class="portal-eyebrow">{{ __('portal.home.greeting', ['name' => str(auth()->user()->name)->before(' ')]) }}</p>
        <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">{{ __('portal.home.title') }}</h1>
    </div>

    <section class="grid gap-4 sm:grid-cols-2">
        @forelse ($this->projects as $project)
            <a href="{{ route('portal.projects.show', $project) }}" wire:navigate wire:key="project-{{ $project->id }}"
                class="portal-card flex flex-col gap-4 p-6">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xl font-bold tracking-tight">{{ $project->name }}</h2>
                    <span class="portal-eyebrow shrink-0 !text-[var(--brass)]">{{ $project->status->label() }}</span>
                </div>

                @if ($project->description)
                    <p class="line-clamp-2 text-sm leading-relaxed text-[var(--silver)]">{{ $project->description }}</p>
                @endif

                <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-2">
                    <p @class([
                        'font-mono text-xs',
                        'text-[var(--ember)]' => $project->outstanding_count > 0,
                        'text-[var(--slate)]' => $project->outstanding_count === 0,
                    ])>
                        {{ trans_choice('portal.home.outstanding', $project->outstanding_count, ['count' => $project->outstanding_count]) }}
                    </p>
                    @if ($project->due_on)
                        <p class="font-mono text-xs text-[var(--slate)]">{{ __('portal.home.due', ['date' => $project->due_on->translatedFormat('j M Y')]) }}</p>
                    @endif
                </div>
            </a>
        @empty
            <p class="text-[var(--silver)] sm:col-span-2">{{ __('portal.home.empty') }}</p>
        @endforelse
    </section>

    @if ($this->recentInvoices->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-baseline justify-between">
                <h2 class="portal-eyebrow">{{ __('portal.home.recent_invoices') }}</h2>
                <a href="{{ route('portal.invoices') }}" wire:navigate class="font-mono text-xs text-[var(--ember)] hover:underline">{{ __('portal.home.all_invoices') }} →</a>
            </div>

            @include('livewire.portal.partials.invoice-list', ['invoices' => $this->recentInvoices])
        </section>
    @endif
</div>
