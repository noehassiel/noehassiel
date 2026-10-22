<div class="mx-auto max-w-6xl">
    {{-- Page header --}}
    <div class="admin-rise mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-eyebrow mb-2">{{ $openCount }} abiertos</p>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--heading)]">Leads</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="admin-rise mb-4 flex flex-col gap-3 sm:flex-row" style="--delay: 1">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, correo o empresa" left-icon="magnifying-glass" class="sm:max-w-sm" />

        <x-ui.select wire:model.live="status" class="sm:max-w-52">
            <option value="">Todos los estados</option>
            @foreach ($statuses as $statusOption)
                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
            @endforeach
        </x-ui.select>
    </div>

    <x-admin.widget class="admin-rise" style="--delay: 2" title="Desde el sitio" subtitle="Formulario de proyecto de la landing" icon="sparkles">
        @forelse ($leads as $lead)
            <a href="{{ route('leads.show', $lead) }}" wire:navigate wire:key="lead-{{ $lead->id }}" class="admin-row">
                <span class="admin-avatar">{{ str($lead->name)->substr(0, 1)->upper() }}</span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm text-[var(--heading)]">
                        {{ $lead->name }}
                        @if ($lead->company)
                            <span class="text-[var(--muted)]">· {{ $lead->company }}</span>
                        @endif
                    </p>
                    <p class="truncate font-mono text-[11px] text-[var(--muted)]">
                        {{ $lead->optionLabel('project_type') }} · {{ $lead->optionLabel('budget') }} · {{ $lead->created_at->diffForHumans(short: true) }}
                    </p>
                </div>

                <x-ui.badge :color="$lead->status->color()" variant="soft" size="sm" class="shrink-0">{{ $lead->status->label() }}</x-ui.badge>
            </a>
        @empty
            <p class="px-4 py-10 text-center text-sm text-[var(--muted)]">
                {{ $search !== '' || $status !== '' ? 'Ningún lead coincide con el filtro.' : 'Aún no hay leads.' }}
            </p>
        @endforelse
    </x-admin.widget>

    @if ($leads->hasPages())
        <div class="mt-4">{{ $leads->links() }}</div>
    @endif
</div>
