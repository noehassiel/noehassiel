<div class="mx-auto max-w-6xl">
    {{-- Page header --}}
    <div class="admin-rise mb-8 flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <a href="{{ route('leads.index') }}" wire:navigate class="admin-eyebrow mb-2 inline-flex items-center gap-1.5 hover:text-[var(--heading)]">
                <x-ui.icon name="arrow-left" class="size-3" /> Leads
            </a>
            <h1 class="truncate text-3xl font-bold tracking-tight text-[var(--heading)]">{{ $lead->name }}</h1>
            <p class="mt-1 text-sm text-[var(--muted)]">
                {{ $lead->company ?: 'Sin empresa' }} · llegó {{ $lead->created_at->diffForHumans() }}
            </p>
        </div>

        @if ($lead->isConverted())
            <a href="{{ route('clients.show', $lead->client) }}" wire:navigate class="admin-btn-primary">
                <x-ui.icon name="user" class="size-4" />
                Ver cliente
            </a>
        @else
            <button type="button" wire:click="convert"
                wire:confirm="¿Cerraste el trato con {{ $lead->name }}? Se crea el cliente (o se vincula al que ya tenga {{ $lead->email }}) y se le pasan sus proposals."
                class="admin-btn-primary cursor-pointer">
                <x-ui.icon name="check-badge" class="size-4" />
                Convertir en cliente
            </button>
        @endif
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            {{-- What they asked for --}}
            <x-admin.widget class="admin-rise" style="--delay: 1" title="Su proyecto" subtitle="Lo que mandó desde la landing" icon="sparkles">
                <dl class="grid grid-cols-1 gap-px sm:grid-cols-3">
                    @foreach (['project_type' => 'Tipo', 'budget' => 'Presupuesto', 'timeline' => 'Tiempo'] as $field => $label)
                        <div class="px-4 py-4">
                            <dt class="admin-eyebrow">{{ $label }}</dt>
                            <dd class="mt-2 text-[var(--heading)]">{{ $lead->optionLabel($field) }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="border-t border-[var(--border)] px-4 py-4">
                    <p class="admin-eyebrow mb-2">Mensaje</p>
                    <p class="whitespace-pre-line leading-relaxed text-[var(--body)]">{{ $lead->message }}</p>
                </div>
            </x-admin.widget>

            {{-- Proposals --}}
            <x-admin.widget class="admin-rise" style="--delay: 2" title="Proposals" subtitle="Enviados a este lead" icon="document-text" :tag="(string) $proposals->count()">
                @forelse ($proposals as $proposal)
                    <a href="{{ route('proposals.show', $proposal) }}" wire:navigate wire:key="proposal-{{ $proposal->id }}" class="admin-row">
                        <div class="min-w-0 flex-1">
                            <p class="font-mono text-[11px] text-[var(--ember)]">{{ $proposal->number }}</p>
                            <p class="truncate text-sm text-[var(--heading)]">{{ $proposal->title }}</p>
                        </div>
                        <span class="admin-chip">{{ $proposal->status->label() }}</span>
                        <span class="shrink-0 font-mono text-sm text-[var(--heading)] tabular-nums">{{ $proposal->currency }} {{ number_format($proposal->total, 0) }}</span>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center">
                        <p class="text-sm text-[var(--muted)]">Aún no le has enviado un proposal.</p>
                        <a href="{{ route('proposals.create') }}" wire:navigate class="mt-2 inline-block font-mono text-xs text-[var(--ember)] hover:underline">Crear proposal →</a>
                    </div>
                @endforelse
            </x-admin.widget>
        </div>

        <div class="space-y-4">
            {{-- Contact --}}
            <x-admin.widget class="admin-rise" style="--delay: 1" title="Contacto" icon="chat-bubble-left-right" :tag="strtoupper($lead->language)">
                <div class="space-y-3 px-4 py-4 text-sm">
                    <a href="mailto:{{ $lead->email }}" class="block truncate text-[var(--ember)] hover:underline">{{ $lead->email }}</a>
                    @if ($lead->phone)
                        <p class="font-mono text-[var(--body)]">{{ $lead->phone }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2 pt-1">
                        <x-ui.button variant="outline" size="sm" icon="envelope" :href="'mailto:'.$lead->email">Correo</x-ui.button>
                        @if ($lead->whatsappUrl())
                            <x-ui.button variant="outline" size="sm" icon="chat-bubble-oval-left" :href="$lead->whatsappUrl()" target="_blank" rel="noopener noreferrer">WhatsApp</x-ui.button>
                        @endif
                    </div>
                </div>
            </x-admin.widget>

            {{-- Pipeline --}}
            <x-admin.widget class="admin-rise" style="--delay: 2" title="Estado" icon="funnel">
                <div class="space-y-4 px-4 py-4">
                    @if ($lead->isConverted())
                        <x-ui.badge color="green" variant="soft">Ganado · {{ $lead->converted_at->translatedFormat('j M Y') }}</x-ui.badge>
                        <p class="text-sm text-[var(--muted)]">Ya es cliente: <a href="{{ route('clients.show', $lead->client) }}" wire:navigate class="text-[var(--ember)] hover:underline">{{ $lead->client->name }}</a></p>
                    @else
                        <x-ui.select wire:model.live="status">
                            @foreach ($statuses as $statusOption)
                                <option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.error name="status" />
                    @endif
                </div>
            </x-admin.widget>

            {{-- Private notes --}}
            <x-admin.widget class="admin-rise" style="--delay: 3" title="Notas" subtitle="Solo tú las ves" icon="pencil-square">
                <form wire:submit="saveNotes" class="space-y-3 px-4 py-4">
                    <x-ui.textarea wire:model="notes" rows="5" placeholder="Qué platicaron, siguiente paso, cuándo darle seguimiento…" />
                    <x-ui.error name="notes" />
                    <div class="flex items-center gap-3">
                        <x-ui.button type="submit" variant="outline" size="sm">Guardar</x-ui.button>
                        <x-action-message on="lead-notes-saved">Guardado.</x-action-message>
                    </div>
                </form>
            </x-admin.widget>
        </div>
    </div>
</div>
