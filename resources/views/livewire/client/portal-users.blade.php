<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="admin-eyebrow">Acceso al portal</h2>

        <x-ui.button wire:click="openInvite" variant="outline" size="sm" icon="user-plus">
            Invitar
        </x-ui.button>
    </div>

    @if ($users->isEmpty() && $invitations->isEmpty())
        <div class="flex flex-col items-center justify-center admin-empty py-10">
            <x-ui.icon name="users" class="mb-2 size-7 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm text-neutral-400">Nadie de este cliente tiene acceso todavía</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($users as $user)
                <div wire:key="portal-user-{{ $user->id }}"
                    class="flex items-center justify-between gap-4 admin-subcard px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $user->name }}</p>
                        <p class="truncate font-mono text-xs text-neutral-400">{{ $user->email }} · {{ strtoupper($user->locale) }}</p>
                        <p class="mt-1 text-xs text-neutral-500">
                            @if ($user->isPortalOwner())
                                <span class="font-medium text-[#FF420B]">Administrador</span> · ve todo y administra su equipo
                            @else
                                <span class="font-medium text-neutral-600 dark:text-neutral-300">Miembro</span> ·
                                {{ $user->can_view_all_projects ? 'todos los proyectos' : ($user->projects->pluck('name')->join(', ') ?: 'ningún proyecto') }}
                                · {{ $user->can_view_invoices ? 've facturas' : 'sin facturas' }}
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-1">
                    <x-ui.button variant="ghost" size="sm" wire:click="editAccess({{ $user->id }})">Permisos</x-ui.button>
                    <x-ui.button
                        variant="ghost"
                        size="sm"
                        wire:click="revoke({{ $user->id }})"
                        wire:confirm="¿Quitarle el acceso a {{ $user->name }}? Su cuenta se elimina."
                        class="shrink-0 text-red-500"
                    >
                        Quitar acceso
                    </x-ui.button>
                    </div>
                </div>
            @endforeach

            @foreach ($invitations as $invitation)
                <div wire:key="portal-invitation-{{ $invitation->id }}"
                    class="flex items-center justify-between gap-4 admin-dashed px-4 py-3 ">
                    <div class="min-w-0">
                        <p class="truncate font-mono text-xs text-neutral-500">{{ $invitation->email }} · {{ strtoupper($invitation->locale) }} · {{ $invitation->portal_role === \App\Enums\PortalRole::Member ? 'miembro' : 'administrador' }}</p>
                        <p class="mt-0.5 text-xs text-neutral-400">
                            @if ($invitation->expires_at->isPast())
                                Invitación expirada
                            @else
                                Invitación pendiente · vence {{ $invitation->expires_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <x-ui.button variant="ghost" size="sm" wire:click="resend({{ $invitation->id }})">Reenviar</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" wire:click="cancelInvitation({{ $invitation->id }})" class="text-red-500">Cancelar</x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Role and access modal --}}
    <x-ui.modal id="portal-access-modal" heading="Permisos en el portal" description="Los administradores ven todo y manejan su equipo. Los miembros ven solo lo que les des." width="md">
        <form wire:submit="saveAccess" class="space-y-5">
            <x-ui.field>
                <x-ui.label>Rol</x-ui.label>
                <x-ui.select wire:model.live="editRole">
                    <x-ui.select.option value="owner">Administrador</x-ui.select.option>
                    <x-ui.select.option value="member">Miembro</x-ui.select.option>
                </x-ui.select>
            </x-ui.field>

            @if ($editRole === 'member')
                <fieldset class="space-y-2">
                    <legend class="mb-1 text-sm font-medium text-neutral-700 dark:text-neutral-300">Proyectos que puede ver</legend>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="editAllProjects" value="1" class="accent-[#FF420B]"> Todos los proyectos
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="radio" wire:model.live="editAllProjects" value="0" class="accent-[#FF420B]"> Solo los seleccionados
                    </label>
                    @if (! $editAllProjects)
                        <div class="grid gap-1.5 pl-6">
                            @forelse ($projects as $project)
                                <label wire:key="access-project-{{ $project->id }}" class="flex cursor-pointer items-center gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    <input type="checkbox" wire:model="editProjectIds" value="{{ $project->id }}" class="accent-[#FF420B]"> {{ $project->name }}
                                </label>
                            @empty
                                <p class="text-sm text-neutral-400">Este cliente aún no tiene proyectos.</p>
                            @endforelse
                        </div>
                        <x-ui.error name="editProjectIds" />
                    @endif
                </fieldset>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                    <input type="checkbox" wire:model="editViewInvoices" class="accent-[#FF420B]"> Puede ver facturas
                </label>
            @endif

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', { id: 'portal-access-modal' })">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="check">Guardar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Invite modal --}}
    <x-ui.modal id="portal-invite-modal" heading="Invitar al portal" description="Le llega un correo para crear su acceso a los proyectos y facturas de {{ $client->name }}." width="md">
        <form wire:submit="invite" class="space-y-5">
            <x-ui.field>
                <x-ui.label>Correo</x-ui.label>
                <x-ui.input wire:model="email" type="email" placeholder="persona@empresa.com" />
                <x-ui.error name="email" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Idioma del portal y los correos</x-ui.label>
                <x-ui.select wire:model="locale">
                    <x-ui.select.option value="es">Español</x-ui.select.option>
                    <x-ui.select.option value="en">English</x-ui.select.option>
                </x-ui.select>
                <x-ui.error name="locale" />
            </x-ui.field>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" variant="outline" x-on:click="$dispatch('close-modal', { id: 'portal-invite-modal' })">Cancelar</x-ui.button>
                <x-ui.button type="submit" variant="primary" icon="paper-airplane">Enviar invitación</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
