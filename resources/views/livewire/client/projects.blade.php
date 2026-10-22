<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400">Proyectos</h2>

        <x-ui.button wire:click="openCreate" variant="outline" size="sm" icon="plus">
            Nuevo proyecto
        </x-ui.button>
    </div>

    @if ($projects->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-neutral-200 py-10 dark:border-neutral-700">
            <x-ui.icon name="folder" class="mb-2 size-7 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm text-neutral-400">Sin proyectos aún</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" wire:navigate wire:key="project-{{ $project->id }}"
                    class="flex items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-white px-4 py-3 transition-colors hover:border-[#FF420B]/30 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:hover:bg-neutral-800/50">
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-neutral-800 dark:text-neutral-200">{{ $project->name }}</p>
                        <p class="mt-0.5 font-mono text-xs text-neutral-400">
                            {{ $project->pending_count }} pendientes
                            @if ($project->to_review_count)
                                · <span class="text-[#FF420B]">{{ $project->to_review_count }} por revisar</span>
                            @endif
                        </p>
                    </div>
                    <x-ui.badge :color="$project->status->color()" variant="soft" size="sm" class="shrink-0">{{ $project->status->label() }}</x-ui.badge>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Create modal --}}
    <x-ui.modal id="project-create-modal" heading="Nuevo proyecto" description="Después agregas el checklist de lo que necesitas de {{ $client->name }}." width="md">
        <form wire:submit="create" class="space-y-5">
            <x-ui.field>
                <x-ui.label>Nombre</x-ui.label>
                <x-ui.input wire:model="name" placeholder="Sitio web corporativo" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Descripción <span class="text-neutral-400">(la ve el cliente)</span></x-ui.label>
                <x-ui.textarea wire:model="description" rows="3" />
                <x-ui.error name="description" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Estado</x-ui.label>
                <x-ui.select wire:model="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.error name="status" />
            </x-ui.field>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', { id: 'project-create-modal' })">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary">Crear proyecto</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
