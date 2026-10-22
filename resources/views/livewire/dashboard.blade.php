@php
    $money = fn (float $amount): string => '$' . number_format($amount, 0);

    // Position of each project status along the delivery path (paused keeps no progress)
    $stageIndex = ['brief' => 1, 'design' => 2, 'development' => 3, 'review' => 4, 'delivered' => 5, 'paused' => 0];

    $statusTone = [
        'draft' => 'bg-[var(--track)]',
        'sent' => 'bg-[var(--brass)]',
        'accepted' => 'bg-[var(--ember)]',
        'rejected' => 'bg-[#4A4A4A]',
        'expired' => 'bg-[#3A3A3A]',
    ];
@endphp

<div class="admin-dashboard mx-auto max-w-6xl">
    {{-- Page header --}}
    <div class="admin-rise mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-eyebrow mb-2">{{ now()->translatedFormat('l d \d\e F') }}</p>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--heading)]">
                Hola, {{ str(auth()->user()->name)->before(' ') }}
            </h1>
        </div>

        <a href="{{ route('proposals.create') }}" wire:navigate class="admin-btn-primary">
            <x-ui.icon name="plus" class="size-4" />
            Nuevo proposal
        </a>
    </div>

    {{-- KPI row --}}
    <div class="admin-rise mb-4 grid grid-cols-2 gap-4 lg:grid-cols-4" style="--delay: 1">
        @foreach ([
            ['label' => 'Pipeline enviado', 'value' => $money($pipelineAmount), 'meta' => 'MXN · proposals enviados'],
            ['label' => 'Ganado ' . now()->year, 'value' => $money($acceptedThisYear), 'meta' => 'MXN · aceptados'],
            ['label' => 'Proyectos activos', 'value' => $activeProjectsCount, 'meta' => 'en curso'],
            ['label' => 'Por cobrar', 'value' => $money($unpaidAmount), 'meta' => $unpaidCount . ' facturas abiertas'],
        ] as $kpi)
            <div class="admin-widget">
                <div class="admin-inset px-4 py-4">
                    <p class="admin-eyebrow">{{ $kpi['label'] }}</p>
                    <p class="mt-3 text-[34px] leading-none font-light tracking-[-0.04em] text-[var(--heading)] tabular-nums">
                        {{ $kpi['value'] }}
                    </p>
                    <p class="mt-2 font-mono text-[11px] text-[var(--muted)]">{{ $kpi['meta'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-4">
        {{-- Proposal pipeline --}}
        <x-admin.widget class="admin-rise lg:col-span-2" style="--delay: 2"
            title="Pipeline de proposals" subtitle="Cuántos hay en cada etapa" icon="document-text" tag="Ahora">
            <div class="space-y-4 px-4 py-4">
                @foreach ($statuses as $status)
                    @php
                        $row = $proposalsByStatus->get($status->value);
                        $count = (int) ($row?->count ?? 0);
                        $ticks = 24;
                        $filled = (int) round($count / $proposalsTotalCount * $ticks);
                    @endphp
                    <div>
                        <div class="mb-1.5 flex items-baseline justify-between">
                            <span class="text-[13px] text-[var(--body)]">{{ $status->label() }}</span>
                            <span @class(['font-mono text-xs tabular-nums', 'text-[var(--muted)]' => $count === 0, 'text-[var(--heading)]' => $count > 0])>
                                {{ $count }}
                                @if ($row?->amount > 0)
                                    <span class="text-[var(--muted)]">· {{ $money((float) $row->amount) }}</span>
                                @endif
                            </span>
                        </div>
                        <div class="admin-ticks">
                            @for ($i = 0; $i < $ticks; $i++)
                                <span @class([$i < $filled ? $statusTone[$status->value] : 'bg-[var(--track)] opacity-60']) style="--i: {{ $i }}"></span>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        </x-admin.widget>

        {{-- Waiting on clients --}}
        <x-admin.widget class="admin-rise lg:col-span-2" style="--delay: 3"
            title="Esperando al cliente" subtitle="Entregables pendientes del checklist" icon="inbox-arrow-down" tag="Ahora">
            @forelse ($waitingOnClients as $requirement)
                <a href="{{ route('projects.show', $requirement->project) }}" wire:navigate class="admin-row">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-[var(--heading)]">{{ $requirement->title }}</p>
                        <p class="truncate text-xs text-[var(--muted)]">{{ $requirement->project->client->name }} · {{ $requirement->project->name }}</p>
                    </div>
                    @if ($requirement->status === \App\Enums\RequirementStatus::ClientMarked)
                        <span class="admin-chip text-[var(--brass)]">Por revisar</span>
                    @elseif ($requirement->due_on)
                        <span @class(['font-mono text-[11px] tabular-nums', 'text-[var(--ember)]' => $requirement->due_on->isPast(), 'text-[var(--muted)]' => ! $requirement->due_on->isPast()])>
                            {{ $requirement->due_on->translatedFormat('d M') }}
                        </span>
                    @endif
                </a>
            @empty
                <p class="px-4 py-10 text-center text-sm text-[var(--muted)]">Nadie te debe nada. Disfrútalo.</p>
            @endforelse
        </x-admin.widget>

        {{-- Active projects --}}
        <x-admin.widget class="admin-rise lg:col-span-3" style="--delay: 4"
            title="Proyectos activos" subtitle="Ordenados por fecha de entrega" icon="rectangle-stack" tag="Ahora">
            @forelse ($activeProjects as $project)
                @php($stage = $stageIndex[$project->status->value])
                <a href="{{ route('projects.show', $project) }}" wire:navigate class="admin-row">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-[var(--heading)]">{{ $project->name }}</p>
                        <p class="truncate text-xs text-[var(--muted)]">
                            {{ $project->client->name }}
                            @if ($project->outstanding_requirements_count)
                                · {{ $project->outstanding_requirements_count }} pendientes
                            @endif
                        </p>
                    </div>

                    <div class="hidden w-40 sm:block">
                        <p class="mb-1.5 text-right font-mono text-[10px] tracking-[0.14em] text-[var(--muted)] uppercase">{{ $project->status->label() }}</p>
                        <div class="admin-ticks admin-ticks-segments">
                            @for ($i = 1; $i <= 5; $i++)
                                <span @class([$i <= $stage ? 'bg-[var(--ember)]' : 'bg-[var(--track)]'])></span>
                            @endfor
                        </div>
                    </div>

                    <span class="w-14 text-right font-mono text-[11px] text-[var(--muted)] tabular-nums">
                        {{ $project->due_on?->translatedFormat('d M') ?? '—' }}
                    </span>
                </a>
            @empty
                <p class="px-4 py-10 text-center text-sm text-[var(--muted)]">Sin proyectos activos por ahora.</p>
            @endforelse
        </x-admin.widget>

        {{-- Recent leads --}}
        <x-admin.widget class="admin-rise" style="--delay: 5"
            title="Leads recientes" subtitle="Desde el sitio" icon="sparkles">
            @forelse ($recentLeads as $lead)
                <div class="admin-row">
                    <span class="admin-avatar">{{ str($lead->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm text-[var(--heading)]">{{ $lead->name }}</p>
                        <p class="truncate font-mono text-[11px] text-[var(--muted)]">{{ $lead->created_at->diffForHumans(short: true) }}</p>
                    </div>
                </div>
            @empty
                <p class="px-4 py-10 text-center text-sm text-[var(--muted)]">Aún no hay leads.</p>
            @endforelse
        </x-admin.widget>
    </div>
</div>
