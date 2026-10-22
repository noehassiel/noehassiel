{{-- What a member can see. $all, $ids and $invoices are the Livewire property names to bind --}}
<fieldset class="space-y-3">
    <legend class="text-sm font-bold">{{ __('portal.team.projects') }}</legend>
    <label class="flex cursor-pointer items-center gap-3">
        <input type="radio" wire:model.live="{{ $all }}" value="1" class="size-4 accent-[var(--ember)]">
        <span>{{ __('portal.team.all_projects') }}</span>
    </label>
    <label class="flex cursor-pointer items-center gap-3">
        <input type="radio" wire:model.live="{{ $all }}" value="0" class="size-4 accent-[var(--ember)]">
        <span>{{ __('portal.team.selected_projects') }}</span>
    </label>

    @if (! $this->{$all})
        @if ($projects->isEmpty())
            <p class="pl-7 text-sm text-[var(--slate)]">{{ __('portal.team.no_projects') }}</p>
        @else
            <div class="grid gap-2 pl-7 sm:grid-cols-2">
                @foreach ($projects as $project)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#2A2A2A] px-3 py-2 has-[:checked]:border-[var(--ember)]">
                        <input type="checkbox" wire:model="{{ $ids }}" value="{{ $project->id }}" class="size-4 accent-[var(--ember)]">
                        <span class="min-w-0 truncate text-sm">{{ $project->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        @error($ids)
            <p role="alert" class="pl-7 text-sm text-[var(--ember)]">{{ __('portal.team.pick_a_project') }}</p>
        @enderror
    @endif
</fieldset>

<label class="flex cursor-pointer items-start gap-3">
    <input type="checkbox" wire:model="{{ $invoices }}" class="mt-1 size-4 accent-[var(--ember)]">
    <span>
        <span class="font-bold">{{ __('portal.team.view_invoices') }}</span>
        <span class="block text-sm text-[var(--silver)]">{{ __('portal.team.view_invoices_hint') }}</span>
    </span>
</label>
