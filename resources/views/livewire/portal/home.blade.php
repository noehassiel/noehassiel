<div class="space-y-8">
    <div>
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">{{ __('portal.home.greeting', ['name' => str(auth()->user()->name)->before(' ')]) }}</p>
        <x-ui.heading level="h1" size="xl" class="mt-1">{{ __('portal.home.title') }}</x-ui.heading>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @forelse ($this->projects as $project)
            <div wire:key="project-{{ $project->id }}" class="space-y-3 rounded-xl border border-neutral-200 p-5 dark:border-neutral-800">
                <div class="flex items-start justify-between gap-3">
                    <p class="font-semibold">{{ $project->name }}</p>
                    <x-ui.badge :color="$project->status->color()" size="sm">{{ $project->status->label() }}</x-ui.badge>
                </div>

                @if ($project->description)
                    <x-ui.text class="text-neutral-500">{{ $project->description }}</x-ui.text>
                @endif

                <p @class([
                    'font-mono text-xs',
                    'text-[#FF420B]' => $project->outstanding_count > 0,
                    'text-neutral-500' => $project->outstanding_count === 0,
                ])>
                    {{ trans_choice('portal.home.outstanding', $project->outstanding_count, ['count' => $project->outstanding_count]) }}
                </p>
            </div>
        @empty
            <x-ui.text class="text-neutral-500">{{ __('portal.home.empty') }}</x-ui.text>
        @endforelse
    </div>
</div>
