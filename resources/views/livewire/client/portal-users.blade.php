<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400">Acceso al portal</h2>

        <x-ui.button wire:click="openInvite" variant="outline" size="sm" icon="user-plus">
            Invitar
        </x-ui.button>
    </div>

    @if ($users->isEmpty() && $invitations->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-neutral-200 py-10 dark:border-neutral-700">
            <x-ui.icon name="users" class="mb-2 size-7 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm text-neutral-400">Nadie de este cliente tiene acceso todavía</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($users as $user)
                <div wire:key="portal-user-{{ $user->id }}"
                    class="flex items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-white px-4 py-3 dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $user->name }}</p>
                        <p class="truncate font-mono text-xs text-neutral-400">{{ $user->email }} · {{ strtoupper($user->locale) }}</p>
                    </div>
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
            @endforeach

            @foreach ($invitations as $invitation)
                <div wire:key="portal-invitation-{{ $invitation->id }}"
                    class="flex items-center justify-between gap-4 rounded-xl border border-dashed border-neutral-200 px-4 py-3 dark:border-neutral-800">
                    <div class="min-w-0">
                        <p class="truncate font-mono text-xs text-neutral-500">{{ $invitation->email }} · {{ strtoupper($invitation->locale) }}</p>
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
                    <option value="es">Español</option>
                    <option value="en">English</option>
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
