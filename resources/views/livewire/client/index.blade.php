<div>
    {{-- Header --}}
    <x-admin.page-header eyebrow="Business" title="Clientes" subtitle="Gestiona tu cartera de clientes">
        <x-slot:actions>
            <x-ui.button variant="primary" wire:click="openCreate" icon="ps:plus">
                Nuevo cliente
            </x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Search --}}
    <div class="mb-6">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email o contacto..."
            class="max-w-sm" leading-icon="magnifying-glass" />
    </div>

    {{-- Grid / Empty state --}}
    @if ($clients->isEmpty())
        <div
            class="flex flex-col items-center justify-center admin-empty py-20">
            <x-ui.icon name="user-group" class="mb-3 size-10 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                @if ($search)
                    Sin resultados para "{{ $search }}"
                @else
                    Aún no hay clientes. ¡Agrega el primero!
                @endif
            </p>
            @if (!$search)
                <x-ui.button variant="outline" wire:click="openCreate" class="mt-4">
                    Agregar cliente
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
                            Cliente</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Contacto</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            Email</th>
                        <th
                            class="px-5 py-3.5 text-left admin-eyebrow">
                            RFC</th>
                        <th
                            class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            Proposals</th>
                        <th class="px-4 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @foreach ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}"
                            class="group transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
                            <td class="px-5 py-4">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate
                                    class="font-semibold text-neutral-900 hover:text-[#FF420B] dark:text-neutral-100">
                                    {{ $client->name }}
                                </a>
                                @if ($client->website)
                                    <p class="mt-0.5 text-xs text-neutral-400">{{ $client->website }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">
                                {{ $client->contact_name ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($client->email)
                                    <a href="mailto:{{ $client->email }}"
                                        class="text-neutral-600 hover:text-[#FF420B] dark:text-neutral-400">
                                        {{ $client->email }}
                                    </a>
                                @else
                                    <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($client->rfc)
                                    <span class="font-mono text-xs text-neutral-500">{{ $client->rfc }}</span>
                                @else
                                    <span class="text-neutral-300 dark:text-neutral-600">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center rounded-full bg-neutral-100 px-2.5 py-0.5 text-xs font-semibold text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                    {{ $client->proposals_count }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div
                                    class="flex items-center justify-end gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <x-ui.button variant="ghost" size="sm" :href="route('clients.show', $client)" wire:navigate>
                                        <x-ui.icon name="eye" class="size-4" />
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm"
                                        wire:click="openEdit({{ $client->id }})">
                                        <x-ui.icon name="pencil-square" class="size-4" />
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm"
                                        wire:click="confirmDelete({{ $client->id }})"
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
            {{ $clients->links() }}
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    <x-ui.modal id="client-modal" :heading="$editingId ? 'Editar cliente' : 'Nuevo cliente'" :open="$showModal">
        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Nombre del cliente / empresa *</x-ui.label>
                    <x-ui.input wire:model="name" placeholder="Grupo Innovar S.A. de C.V." />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Nombre de contacto</x-ui.label>
                    <x-ui.input wire:model="contactName" placeholder="Lic. María García" />
                    <x-ui.error name="contactName" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Teléfono</x-ui.label>
                    <x-ui.input wire:model="phone" type="tel" placeholder="+52 222 123 4567" />
                    <x-ui.error name="phone" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Email</x-ui.label>
                    <x-ui.input wire:model="email" type="email" placeholder="contacto@empresa.com" />
                    <x-ui.error name="email" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>RFC</x-ui.label>
                    <x-ui.input wire:model="rfc" placeholder="GIN901231AB1" class="uppercase" />
                    <x-ui.error name="rfc" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Sitio web</x-ui.label>
                    <x-ui.input wire:model="website" type="url" placeholder="https://empresa.com" />
                    <x-ui.error name="website" />
                </x-ui.field>

                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Dirección</x-ui.label>
                    <x-ui.input wire:model="address" placeholder="Av. Reforma 123, Colonia Centro, Puebla" />
                    <x-ui.error name="address" />
                </x-ui.field>

                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Notas internas</x-ui.label>
                    <x-ui.textarea wire:model="notes" rows="3"
                        placeholder="Observaciones, preferencias, datos relevantes..." />
                    <x-ui.error name="notes" />
                </x-ui.field>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" wire:click="closeModal">Cancelar</x-ui.button>
            <x-ui.button variant="primary" wire:click="save">
                {{ $editingId ? 'Guardar cambios' : 'Crear cliente' }}
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- Delete confirmation --}}
    @if ($deletingId)
        <x-ui.modal id="confirm-delete-client" heading="Eliminar cliente" x-init="open()">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                ¿Estás seguro? Se eliminarán también todos los proposals e información asociada a este cliente.
            </p>
            <x-slot:footer>
                <x-ui.button variant="outline" wire:click="cancelDelete">Cancelar</x-ui.button>
                <x-ui.button variant="danger" wire:click="delete">Sí, eliminar</x-ui.button>
            </x-slot:footer>
        </x-ui.modal>
    @endif
</div>
