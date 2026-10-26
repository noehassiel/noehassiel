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
        <section class="min-w-0 space-y-5 lg:col-span-3" aria-labelledby="checklist-heading">
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

                            @if ($requirement->files->isNotEmpty() || $requirement->status !== RequirementStatus::Received)
                                <div class="w-full space-y-3 border-t border-[#1C1C1C] pt-4">
                                    @if ($requirement->files->isNotEmpty())
                                        <ul class="space-y-2">
                                            @foreach ($requirement->files as $file)
                                                <li wire:key="file-{{ $file->id }}" class="flex items-center gap-3 text-sm">
                                                    <a href="{{ route('requirement-files.download', $file) }}"
                                                        class="min-w-0 flex-1 truncate underline decoration-[#3A3A3A] underline-offset-4 hover:decoration-[var(--ember)]">{{ $file->original_name }}</a>
                                                    <span class="shrink-0 font-mono text-xs text-[var(--slate)]">{{ $file->humanSize() }}</span>
                                                    @can('removeRequirementFile', [$project, $file])
                                                        <button type="button" wire:click="removeFile({{ $file->id }})" wire:confirm="{{ __('portal.project.remove_file_confirm') }}"
                                                            class="shrink-0 cursor-pointer font-mono text-xs text-[var(--slate)] hover:text-[var(--ember)]">{{ __('portal.project.remove_file') }}</button>
                                                    @endcan
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($requirement->status !== RequirementStatus::Received)
                                        <form wire:submit="uploadFiles({{ $requirement->id }})" class="flex flex-wrap items-center gap-3">
                                            <label class="min-w-0 flex-1 text-sm">
                                                <span class="sr-only">{{ __('portal.project.files') }}</span>
                                                <input type="file" multiple wire:model="uploads.{{ $requirement->id }}"
                                                    class="block w-full cursor-pointer text-sm text-[var(--silver)] file:mr-3 file:cursor-pointer file:rounded-full file:border file:border-[#2A2A2A] file:bg-transparent file:px-4 file:py-2 file:font-mono file:text-xs file:uppercase file:tracking-[0.12em] file:text-[var(--stone)] hover:file:border-[var(--ember)]">
                                            </label>
                                            <button type="submit" wire:loading.attr="disabled" wire:target="uploads.{{ $requirement->id }}, uploadFiles"
                                                @disabled(empty($uploads[$requirement->id]))
                                                class="shrink-0 cursor-pointer rounded-full bg-[var(--ember)] px-4 py-2 font-mono text-xs font-semibold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[var(--ember-deep)] disabled:cursor-not-allowed disabled:opacity-60">
                                                <span wire:loading.remove wire:target="uploads.{{ $requirement->id }}, uploadFiles">{{ __('portal.project.upload') }}</span>
                                                <span wire:loading wire:target="uploads.{{ $requirement->id }}, uploadFiles">{{ __('portal.project.uploading') }}</span>
                                            </button>
                                        </form>
                                        <p class="font-mono text-xs text-[var(--slate)]">{{ __('portal.project.upload_hint') }}</p>
                                        @error("uploads.{$requirement->id}") <p class="text-sm text-[var(--ember)]">{{ $message }}</p> @enderror
                                        @error("uploads.{$requirement->id}.*") <p class="text-sm text-[var(--ember)]">{{ $message }}</p> @enderror
                                    @endif
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Shared folders --}}
        <section class="min-w-0 space-y-5 lg:col-span-2" aria-labelledby="folders-heading">
            <div>
                <h2 id="folders-heading" class="text-2xl font-bold tracking-tight">{{ __('portal.project.folders') }}</h2>
                <p class="mt-2 text-sm text-[var(--silver)]">{{ __('portal.project.folders_hint') }}</p>
            </div>

            @if ($links->isEmpty())
                <p class="text-sm text-[var(--slate)]">{{ __('portal.project.folders_empty') }}</p>
            @else
                <ul class="space-y-3">
                    @foreach ($links as $link)
                        <li wire:key="link-{{ $link->id }}" class="portal-card flex items-center gap-4 p-5">
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="flex min-w-0 flex-1 items-center justify-between gap-4">
                                <span class="min-w-0">
                                    <span class="block truncate font-bold">{{ $link->label }}</span>
                                    <span class="mt-1 block font-mono text-xs text-[var(--slate)]">
                                        {{ $link->providerName() }}@if ($link->added_by === auth()->id()) · {{ __('portal.project.added_by_you') }}@endif
                                    </span>
                                </span>
                                <span class="shrink-0 font-mono text-xs uppercase tracking-[0.14em] text-[var(--ember)]">{{ __('portal.project.open_folder') }} ↗</span>
                            </a>
                            @can('removeLink', [$project, $link])
                                <button type="button" wire:click="removeLink({{ $link->id }})" wire:confirm="{{ __('portal.project.remove_folder_confirm') }}"
                                    class="shrink-0 cursor-pointer font-mono text-xs uppercase tracking-[0.14em] text-[var(--slate)] transition-colors hover:text-[var(--stone)]">
                                    {{ __('portal.project.remove_folder') }}
                                </button>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif

            @can('addLink', $project)
                <form wire:submit="addLink" class="portal-card space-y-4 p-5" aria-labelledby="add-folder-heading">
                    <h3 id="add-folder-heading" class="portal-eyebrow">{{ __('portal.project.add_folder') }}</h3>

                    <div class="grid gap-4">
                        <div class="flex min-w-0 flex-col gap-2">
                            <label for="link-label" class="text-sm font-bold">{{ __('portal.project.folder_name') }}</label>
                            <input id="link-label" wire:model="linkLabel" type="text" required maxlength="255"
                                placeholder="{{ __('portal.project.folder_name_placeholder') }}"
                                @error('linkLabel') aria-invalid="true" aria-describedby="link-label-error" @enderror
                                class="w-full min-w-0 rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 text-[var(--stone)] placeholder:text-[#3A3A3A]">
                            @error('linkLabel')
                                <p id="link-label-error" role="alert" class="text-sm text-[var(--ember)]">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex min-w-0 flex-col gap-2">
                            <label for="link-url" class="text-sm font-bold">{{ __('portal.project.folder_url') }}</label>
                            <input id="link-url" wire:model="linkUrl" type="url" inputmode="url" required placeholder="https://"
                                aria-describedby="link-url-hint @error('linkUrl') link-url-error @enderror"
                                @error('linkUrl') aria-invalid="true" @enderror
                                class="w-full min-w-0 rounded-xl border border-[#2A2A2A] bg-[#0D0D0D] px-4 py-3 font-mono text-sm text-[var(--stone)] placeholder:text-[#3A3A3A]">
                            <p id="link-url-hint" class="text-xs text-[var(--slate)]">{{ __('portal.project.folder_url_hint') }}</p>
                            @error('linkUrl')
                                <p id="link-url-error" role="alert" class="text-sm text-[var(--ember)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="cursor-pointer rounded-xl bg-[var(--ember)] px-5 py-3 font-bold text-[var(--obsidian)] transition-opacity hover:opacity-90 disabled:opacity-60">
                        {{ __('portal.project.add_folder') }}
                    </button>
                </form>
            @endcan
        </section>
    </div>
</div>
