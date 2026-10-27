<div>
    {{-- Header --}}
    <x-admin.page-header eyebrow="Business" title="Proposals" subtitle="Gestiona y envía tus propuestas de proyecto">
        <x-slot:actions>
            <x-ui.button variant="primary" :href="route('proposals.create')" wire:navigate icon="ps:plus">
                Nuevo proposal
            </x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filters --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar por número, título o cliente..."
            class="sm:max-w-xs" leading-icon="magnifying-glass" />

        <x-ui.select wire:model.live="statusFilter" placeholder="Todos los estados" clearable class="sm:max-w-48">
            @foreach ($this->statuses as $status)
                <x-ui.select.option value="{{ $status->value }}">{{ $status->label() }}</x-ui.select.option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Table --}}
    @if ($proposals->isEmpty())
        <div
            class="flex flex-col items-center justify-center admin-empty py-20">
            <x-ui.icon name="document-text" class="mb-3 size-10 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                @if ($search || $statusFilter)
                    Sin resultados para tu búsqueda
                @else
                    Aún no hay proposals. ¡Crea el primero!
                @endif
            </p>
            @if (!$search && !$statusFilter)
                <x-ui.button variant="outline" :href="route('proposals.create')" wire:navigate class="mt-4">
                    Crear proposal
                </x-ui.button>
            @endif
        </div>
    @else
        <div class="admin-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900">
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Número</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Título</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Cliente</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Estado</th>
                        <th
                            class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Total</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Vigencia</th>
                        <th class="px-4 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @foreach ($proposals as $proposal)
                        <tr wire:key="proposal-{{ $proposal->id }}"
                            class="group transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                            <td class="px-5 py-4">
                                <a href="{{ route('proposals.show', $proposal) }}" wire:navigate
                                    class="font-mono text-xs font-semibold text-[#FF420B] hover:underline">
                                    {{ $proposal->number }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('proposals.show', $proposal) }}" wire:navigate
                                    class="font-medium text-neutral-900 hover:text-[#FF420B] dark:text-neutral-100">
                                    {{ $proposal->title }}
                                </a>
                                <p class="mt-0.5 text-xs text-neutral-400">{{ $proposal->project_type->label() }}</p>
                            </td>
                            <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">{{ $proposal->recipient()?->name }}
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $colors = [
                                        'zinc' =>
                                            'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
                                        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                        'green' =>
                                            'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                        'red' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                        'amber' =>
                                            'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    ];
                                    $colorClass = $colors[$proposal->status->color()] ?? $colors['zinc'];
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorClass }}">
                                    {{ $proposal->status->label() }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-4 text-right font-mono text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ $proposal->currency }} {{ number_format($proposal->total, 2) }}
                            </td>
                            <td class="px-5 py-4 text-xs text-neutral-400">
                                {{ $proposal->valid_until?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-4">
                                <div
                                    class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <x-ui.button variant="ghost" size="sm" :href="route('proposals.show', $proposal)" wire:navigate>
                                        <x-ui.icon name="eye" class="size-4" />
                                    </x-ui.button>
                                    @unless ($proposal->isLocked())
                                        <x-ui.button variant="ghost" size="sm" :href="route('proposals.edit', $proposal)" wire:navigate>
                                            <x-ui.icon name="pencil-square" class="size-4" />
                                        </x-ui.button>
                                    @endunless
                                    <x-ui.button variant="ghost" size="sm"
                                        wire:click="confirmDelete({{ $proposal->id }})"
                                        class="text-red-500 hover:text-red-600">
                                        <x-ui.icon name="trash" class="size-4" />
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $proposals->links() }}
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($deletingId)
        <x-ui.modal id="confirm-delete" heading="Eliminar proposal" :open="true">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                ¿Estás seguro de que quieres eliminar este proposal? Esta acción no se puede deshacer.
            </p>

            <x-slot:footer>
                <x-ui.button variant="outline" wire:click="cancelDelete">Cancelar</x-ui.button>
                <x-ui.button variant="danger" wire:click="delete">Sí, eliminar</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
