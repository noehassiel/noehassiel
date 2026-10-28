<div>
    {{-- Header --}}
    <x-admin.page-header :back="route('clients.show', $project->client)" :back-label="$project->client->name" :title="$project->name">
        <x-slot:meta>
            <x-ui.badge :color="$project->status->color()" variant="soft" class="shrink-0">{{ $project->status->label() }}</x-ui.badge>
        </x-slot:meta>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Details --}}
        <div class="space-y-6">
            <form wire:submit="saveDetails" class="space-y-4 admin-card p-6">
                <h2 class="admin-eyebrow">Detalles</h2>

                <x-ui.field>
                    <x-ui.label>Nombre</x-ui.label>
                    <x-ui.input wire:model="name" />
                    <x-ui.error name="name" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Estado</x-ui.label>
                    <x-ui.select wire:model="status">
                        @foreach ($statuses as $statusOption)
                            <x-ui.select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.error name="status" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Descripción</x-ui.label>
                    <x-ui.textarea wire:model="description" rows="3" />
                    <x-ui.error name="description" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Nota de avance <span class="text-neutral-400">(la ve el cliente)</span></x-ui.label>
                    <x-ui.textarea wire:model="progressNote" rows="3" placeholder="Terminamos el diseño de la home; esta semana arrancamos desarrollo." />
                    <x-ui.error name="progressNote" />
                </x-ui.field>

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field>
                        <x-ui.label>Inicio</x-ui.label>
                        <x-ui.date-picker wire:model="startsOn" label="Selecciona una fecha" locale="es-MX" clearable />
                        <x-ui.error name="startsOn" />
                    </x-ui.field>
                    <x-ui.field>
                        <x-ui.label>Entrega</x-ui.label>
                        <x-ui.date-picker wire:model="dueOn" label="Selecciona una fecha" locale="es-MX" clearable />
                        <x-ui.error name="dueOn" />
                    </x-ui.field>
                </div>

                <x-ui.field>
                    <x-ui.label>Vista previa <span class="text-neutral-400">(URL del sitio en tu servidor de previews)</span></x-ui.label>
                    <x-ui.input wire:model="previewUrl" type="url" placeholder="https://cliente.preview.noehassiel.com" />
                    <x-ui.error name="previewUrl" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Rondas de cambios incluidas</x-ui.label>
                    <x-ui.input wire:model="revisionRounds" type="number" min="0" max="10" />
                    <x-ui.error name="revisionRounds" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Lo que el cliente edita solo <span class="text-neutral-400">(la IA se lo explica en vez de abrir un issue)</span></x-ui.label>
                    <x-ui.textarea wire:model="selfServiceNotes" rows="3" placeholder="Banners del inicio y eventos, desde /admin." />
                    <x-ui.error name="selfServiceNotes" />
                </x-ui.field>

                <div class="flex items-center gap-3">
                    <x-ui.button type="submit" variant="primary" size="sm">Guardar</x-ui.button>
                    <x-action-message on="project-saved">Guardado.</x-action-message>
                </div>

                @if ($project->preview_url)
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-800 pt-4 text-sm">
                        @if ($project->approved_at)
                            <span class="text-neutral-400">Aprobado el {{ $project->approved_at->format('d/m/Y') }} por {{ $project->approver?->name }}</span>
                        @else
                            <span class="text-neutral-400">Ronda {{ $project->current_round }} de {{ $project->revision_rounds }}</span>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="startNextRound"
                                wire:confirm="¿Publicaste una nueva versión de la preview? Lo que pida el cliente desde ahora cuenta como la siguiente ronda.">
                                Nueva versión publicada
                            </x-ui.button>
                        @endif
                    </div>
                @endif
            </form>

            <x-ui.button variant="ghost" size="sm" icon="trash" class="text-red-500"
                wire:click="deleteProject"
                wire:confirm="¿Eliminar «{{ $project->name }}»? Se borran también su checklist y sus carpetas.">
                Eliminar proyecto
            </x-ui.button>
        </div>

        <div class="space-y-8 lg:col-span-2">

            {{-- Checklist --}}
            <div class="space-y-4">
                <div>
                    <h2 class="admin-eyebrow">Checklist de entregables</h2>
                    <p class="mt-1 text-xs text-neutral-400">Lo que necesitas del cliente. Mientras haya pendientes, se le recuerda por correo.</p>
                </div>

                @if ($requirements->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($requirements as $requirement)
                            <div wire:key="requirement-{{ $requirement->id }}" @class([
                                'flex items-start justify-between gap-4 rounded-xl border bg-white px-4 py-3 dark:bg-neutral-900',
                                'border-[#FF420B]/40' => $requirement->status === \App\Enums\RequirementStatus::ClientMarked,
                                'border-neutral-200 dark:border-neutral-800' => $requirement->status !== \App\Enums\RequirementStatus::ClientMarked,
                            ])>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p @class([
                                            'font-medium text-neutral-800 dark:text-neutral-200',
                                            'line-through opacity-60' => $requirement->status === \App\Enums\RequirementStatus::Received,
                                        ])>{{ $requirement->title }}</p>
                                        <x-ui.badge :color="$requirement->status->color()" variant="soft" size="sm">{{ $requirement->status->label() }}</x-ui.badge>
                                    </div>
                                    @if ($requirement->description)
                                        <p class="mt-1 text-sm text-neutral-500">{{ $requirement->description }}</p>
                                    @endif
                                    <p class="mt-1 font-mono text-xs text-neutral-400">
                                        @if ($requirement->due_on)
                                            Para el {{ $requirement->due_on->format('d/m/Y') }}
                                        @endif
                                        @if ($requirement->client_marked_at && $requirement->status === \App\Enums\RequirementStatus::ClientMarked)
                                            · El cliente lo marcó como subido {{ $requirement->client_marked_at->diffForHumans() }}
                                        @endif
                                    </p>
                                    @if ($requirement->files->isNotEmpty())
                                        <ul class="mt-3 space-y-1.5">
                                            @foreach ($requirement->files as $file)
                                                <li wire:key="file-{{ $file->id }}" class="flex items-center gap-2 text-sm">
                                                    <x-ui.icon name="paper-clip" class="size-4 shrink-0 text-neutral-400" />
                                                    <a href="{{ route('requirement-files.download', $file) }}" class="min-w-0 truncate underline underline-offset-4 hover:text-[#FF420B]">{{ $file->original_name }}</a>
                                                    <span class="shrink-0 font-mono text-xs text-neutral-400">{{ $file->humanSize() }} · {{ $file->uploader?->name ?? 'Cliente' }} · {{ $file->created_at->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    @if ($requirement->status === \App\Enums\RequirementStatus::Received)
                                        <x-ui.button variant="ghost" size="sm" wire:click="markPending({{ $requirement->id }})">Reabrir</x-ui.button>
                                    @else
                                        @if ($requirement->status === \App\Enums\RequirementStatus::ClientMarked)
                                            <x-ui.button variant="ghost" size="sm" wire:click="markPending({{ $requirement->id }})">Aún falta</x-ui.button>
                                        @endif
                                        <x-ui.button variant="outline" size="sm" icon="check" wire:click="markReceived({{ $requirement->id }})">Recibido</x-ui.button>
                                    @endif
                                    <x-ui.button variant="ghost" size="sm" icon="trash" squared aria-label="Eliminar punto"
                                        wire:click="removeRequirement({{ $requirement->id }})"
                                        wire:confirm="¿Eliminar «{{ $requirement->title }}» del checklist?" class="text-red-500" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form wire:submit="addRequirement" class="space-y-3 admin-dashed p-4 ">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <x-ui.field class="sm:col-span-2">
                            <x-ui.input wire:model="requirementTitle" placeholder="Logo en vector (AI, SVG o PDF)" />
                            <x-ui.error name="requirementTitle" />
                        </x-ui.field>
                        <x-ui.field>
                            <x-ui.date-picker wire:model="requirementDueOn" label="Selecciona una fecha" locale="es-MX" clearable />
                            <x-ui.error name="requirementDueOn" />
                        </x-ui.field>
                    </div>
                    <x-ui.field>
                        <x-ui.input wire:model="requirementDescription" placeholder="Detalle opcional: formato, medidas, dónde subirlo…" />
                        <x-ui.error name="requirementDescription" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="outline" size="sm" icon="plus">Agregar al checklist</x-ui.button>
                </form>
            </div>

            {{-- Linear --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="admin-eyebrow">Linear</h2>
                    @if ($project->linear_project_url)
                        <x-ui.button variant="ghost" size="sm" :href="$project->linear_project_url" target="_blank" iconAfter="arrow-top-right-on-square">Abrir en Linear</x-ui.button>
                    @elseif ($linearConfigured)
                        <x-ui.button variant="outline" size="sm" wire:click="syncToLinear" wire:loading.attr="disabled" wire:target="syncToLinear" icon="arrow-path">Crear en Linear</x-ui.button>
                    @endif
                </div>
                @unless ($linearConfigured)
                    <p class="text-xs text-neutral-400">Agrega LINEAR_API_KEY y LINEAR_TEAM_ID al .env para mandar proyectos y solicitudes a Linear.</p>
                @endunless
            </div>

            {{-- Client requests --}}
            <div class="space-y-3">
                <div>
                    <h2 class="admin-eyebrow">Solicitudes del cliente</h2>
                    <p class="mt-1 text-xs text-neutral-400">Lo que el cliente reporta desde su portal. El estado se actualiza desde Linear.</p>
                </div>
                @forelse ($tickets as $ticket)
                    <div wire:key="admin-ticket-{{ $ticket->id }}" class="admin-subcard px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 text-sm font-semibold text-neutral-800 dark:text-neutral-200">{{ $ticket->title }}</p>
                            <span class="shrink-0 text-xs text-neutral-500">{{ $ticket->status->label() }}</span>
                        </div>
                        <p class="mt-0.5 text-xs text-neutral-400">
                            {{ $ticket->type->label() }} · {{ $ticket->created_at->format('d/m/Y') }}@if ($ticket->user) · {{ $ticket->user->name }}@endif
                            @if ($ticket->linear_url)
                                · <a href="{{ $ticket->linear_url }}" target="_blank" rel="noopener" class="font-mono text-[#FF420B] hover:underline">{{ $ticket->linear_identifier }}</a>
                            @elseif ($linearConfigured)
                                · <button type="button" wire:click="retryTicket({{ $ticket->id }})" class="cursor-pointer text-[#FF420B] hover:underline">Mandar a Linear</button>
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-neutral-400">Sin solicitudes.</p>
                @endforelse
            </div>

            {{-- Shared folders --}}
            <div class="space-y-4">
                <div>
                    <h2 class="admin-eyebrow">Carpetas compartidas</h2>
                    <p class="mt-1 text-xs text-neutral-400">Donde el cliente sube los recursos. El cliente las ve en su portal.</p>
                </div>

                @if ($links->isNotEmpty())
                    <div class="space-y-2">
                        @foreach ($links as $link)
                            <div wire:key="link-{{ $link->id }}"
                                class="flex items-center justify-between gap-4 admin-subcard px-4 py-3">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="min-w-0 hover:text-[#FF420B]">
                                    <p class="truncate font-medium text-neutral-800 dark:text-neutral-200">{{ $link->label }}</p>
                                    <p class="truncate font-mono text-xs text-neutral-400">{{ $link->providerName() }}</p>
                                </a>
                                <x-ui.button variant="ghost" size="sm" icon="trash" squared aria-label="Quitar carpeta"
                                    wire:click="removeLink({{ $link->id }})" class="shrink-0 text-red-500" />
                            </div>
                        @endforeach
                    </div>
                @endif

                <form wire:submit="addLink" class="grid gap-3 admin-dashed p-4 sm:grid-cols-3 ">
                    <x-ui.field>
                        <x-ui.input wire:model="linkLabel" placeholder="Fotografías" />
                        <x-ui.error name="linkLabel" />
                    </x-ui.field>
                    <x-ui.field class="sm:col-span-2">
                        <x-ui.input wire:model="linkUrl" type="url" placeholder="https://drive.google.com/drive/folders/…" />
                        <x-ui.error name="linkUrl" />
                    </x-ui.field>
                    <div class="sm:col-span-3">
                        <x-ui.button type="submit" variant="outline" size="sm" icon="link">Agregar carpeta</x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
