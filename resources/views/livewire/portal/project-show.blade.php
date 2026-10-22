@php
    use App\Enums\ProjectStatus;
    use App\Enums\RequirementStatus;

    $pendingCount = $requirements->where('status', RequirementStatus::Pending)->count();
@endphp

<div class="space-y-14">
    {{-- Header --}}
    <div>
        <a href="{{ route('portal.home') }}" wire:navigate class="portal-eyebrow transition-colors hover:text-[var(--stone)]">
            ← {{ __('portal.project.back') }}
        </a>
        <h1 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">{{ $project->name }}</h1>
        @if ($project->description)
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-[var(--silver)]">{{ $project->description }}</p>
        @endif
    </div>

    {{-- Progress --}}
    <section class="portal-card space-y-6 p-6 sm:p-8" aria-labelledby="progress-heading">
        <div class="flex flex-wrap items-baseline justify-between gap-3">
            <h2 id="progress-heading" class="portal-eyebrow">{{ __('portal.project.progress') }}</h2>
            <p class="font-bold text-[var(--ember)]">{{ $project->status->label() }}</p>
        </div>

        @if ($project->status !== ProjectStatus::Paused)
            <ol class="grid gap-2" style="grid-template-columns: repeat({{ count($steps) }}, minmax(0, 1fr));">
                @foreach ($steps as $index => $step)
                    @php($state = $index < $currentStep ? 'done' : ($index === $currentStep ? 'current' : 'upcoming'))
                    <li class="space-y-2" @if ($state === 'current') aria-current="step" @endif>
                        <div class="portal-step" data-state="{{ $state }}"></div>
                        <p @class([
                            'hidden font-mono text-[10px] uppercase tracking-[0.12em] sm:block',
                            'text-[var(--stone)]' => $state !== 'upcoming',
                            'text-[var(--slate)]' => $state === 'upcoming',
                        ])>{{ $step->label() }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($project->progress_note)
            <p class="max-w-2xl whitespace-pre-line leading-relaxed">{{ $project->progress_note }}</p>
        @endif

        @if ($project->starts_on || $project->due_on)
            <dl class="flex flex-wrap gap-x-10 gap-y-3 border-t border-[#1C1C1C] pt-5">
                @if ($project->starts_on)
                    <div>
                        <dt class="portal-eyebrow">{{ __('portal.project.starts') }}</dt>
                        <dd class="mt-1 font-mono text-sm">{{ $project->starts_on->translatedFormat('j M Y') }}</dd>
                    </div>
                @endif
                @if ($project->due_on)
                    <div>
                        <dt class="portal-eyebrow">{{ __('portal.project.due') }}</dt>
                        <dd class="mt-1 font-mono text-sm">{{ $project->due_on->translatedFormat('j M Y') }}</dd>
                    </div>
                @endif
            </dl>
        @endif
    </section>

    <div class="grid gap-10 lg:grid-cols-5">
        {{-- Checklist --}}
        <section class="space-y-5 lg:col-span-3" aria-labelledby="checklist-heading">
            <div>
                <h2 id="checklist-heading" class="text-2xl font-bold tracking-tight">{{ __('portal.project.checklist') }}</h2>
                @if ($pendingCount > 0)
                    <p class="mt-2 text-sm text-[var(--silver)]">{{ __('portal.project.checklist_hint') }}</p>
                @endif
            </div>

            @if ($requirements->isEmpty())
                <p class="text-[var(--silver)]">{{ __('portal.project.checklist_empty') }}</p>
            @else
                @if ($pendingCount === 0 && $requirements->every(fn ($requirement) => $requirement->status === RequirementStatus::Received))
                    <p class="font-bold text-[var(--brass)]">{{ __('portal.project.all_received') }}</p>
                @endif

                <ul class="space-y-3">
                    @foreach ($requirements as $requirement)
                        <li wire:key="requirement-{{ $requirement->id }}" @class([
                            'portal-card flex flex-wrap items-start justify-between gap-4 p-5',
                            '!border-[color-mix(in_srgb,var(--ember)_40%,transparent)]' => $requirement->status === RequirementStatus::Pending,
                        ])>
                            <div class="min-w-0 flex-1">
                                <p @class([
                                    'font-bold',
                                    'text-[var(--slate)] line-through' => $requirement->status === RequirementStatus::Received,
                                ])>{{ $requirement->title }}</p>

                                @if ($requirement->description && $requirement->status !== RequirementStatus::Received)
                                    <p class="mt-1 text-sm leading-relaxed text-[var(--silver)]">{{ $requirement->description }}</p>
                                @endif

                                <p class="mt-2 font-mono text-xs text-[var(--slate)]">
                                    @switch($requirement->status)
                                        @case(RequirementStatus::Received)
                                            {{ __('portal.project.received_at', ['time' => $requirement->received_at?->diffForHumans()]) }}
                                            @break
                                        @case(RequirementStatus::ClientMarked)
                                            <span class="text-[var(--brass)]">{{ __('portal.project.marked_at', ['time' => $requirement->client_marked_at?->diffForHumans()]) }}</span>
                                            @break
                                        @default
                                            @if ($requirement->due_on)
                                                <span @class(['text-[var(--ember)]' => $requirement->due_on->isPast()])>
                                                    {{ __('portal.project.due_on', ['date' => $requirement->due_on->translatedFormat('j M Y')]) }}
                                                </span>
                                            @endif
                                    @endswitch
                                </p>
                            </div>

                            @if ($requirement->status === RequirementStatus::Pending)
                                <button type="button" wire:click="markUploaded({{ $requirement->id }})" wire:loading.attr="disabled"
                                    class="shrink-0 cursor-pointer rounded-full bg-[var(--ember)] px-4 py-2 font-mono text-xs font-semibold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[var(--ember-deep)] disabled:opacity-60">
                                    {{ __('portal.project.mark_uploaded') }}
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Shared folders --}}
        <section class="space-y-5 lg:col-span-2" aria-labelledby="folders-heading">
            <div>
                <h2 id="folders-heading" class="text-2xl font-bold tracking-tight">{{ __('portal.project.folders') }}</h2>
                <p class="mt-2 text-sm text-[var(--silver)]">{{ __('portal.project.folders_hint') }}</p>
            </div>

            @if ($links->isEmpty())
                <p class="text-sm text-[var(--slate)]">{{ __('portal.project.folders_empty') }}</p>
            @else
                <ul class="space-y-3">
                    @foreach ($links as $link)
                        <li wire:key="link-{{ $link->id }}">
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                                class="portal-card flex items-center justify-between gap-4 p-5">
                                <span class="min-w-0">
                                    <span class="block truncate font-bold">{{ $link->label }}</span>
                                    <span class="mt-1 block font-mono text-xs text-[var(--slate)]">{{ $link->providerName() }}</span>
                                </span>
                                <span class="shrink-0 font-mono text-xs uppercase tracking-[0.14em] text-[var(--ember)]">{{ __('portal.project.open_folder') }} ↗</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
