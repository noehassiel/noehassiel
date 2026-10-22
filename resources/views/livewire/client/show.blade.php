<div>
    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between">
        <div>
            <a href="{{ route('clients.index') }}" wire:navigate
                class="mb-1 inline-flex items-center gap-1.5 text-xs font-medium text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                <x-ui.icon name="arrow-left" class="size-3.5" />
                Todos los clientes
            </a>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $client->name }}</h1>
            @if ($client->contact_name)
                <p class="mt-1 text-sm text-neutral-500">Contacto: {{ $client->contact_name }}</p>
            @endif
        </div>

        <x-ui.button variant="outline" size="sm" :href="route('proposals.create')" wire:navigate icon="document-plus">
            Nuevo proposal
        </x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Client info --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-neutral-400">Información</h2>

                <dl class="space-y-3 text-sm">
                    @if ($client->email)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Email</dt>
                            <dd class="mt-0.5"><a href="mailto:{{ $client->email }}"
                                    class="text-[#FF420B] hover:underline">{{ $client->email }}</a></dd>
                        </div>
                    @endif

                    @if ($client->phone)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Teléfono</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">{{ $client->phone }}</dd>
                        </div>
                    @endif

                    @if ($client->rfc)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">RFC</dt>
                            <dd class="mt-0.5 font-mono text-xs text-neutral-700 dark:text-neutral-300">
                                {{ $client->rfc }}</dd>
                        </div>
                    @endif

                    @if ($client->address)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Dirección</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">{{ $client->address }}</dd>
                        </div>
                    @endif

                    @if ($client->website)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Sitio web</dt>
                            <dd class="mt-0.5"><a href="{{ $client->website }}" target="_blank"
                                    class="text-[#FF420B] hover:underline">{{ $client->website }}</a></dd>
                        </div>
                    @endif

                    @if ($client->notes)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Notas</dt>
                            <dd class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">{{ $client->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-3">
                <div
                    class="rounded-2xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                        {{ $client->proposals->count() }}</div>
                    <div class="mt-0.5 text-xs text-neutral-400">Proposals</div>
                </div>
                <div
                    class="rounded-2xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                        {{ $client->invoices->count() }}</div>
                    <div class="mt-0.5 text-xs text-neutral-400">Facturas</div>
                </div>
            </div>
        </div>

        {{-- Projects, Proposals & Invoices --}}
        <div class="space-y-8 lg:col-span-2">

            {{-- Projects section --}}
            <livewire:client.projects :client="$client" />

            {{-- Proposals section --}}
            <div class="space-y-4">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400">Proposals</h2>

                @if ($client->proposals->isEmpty())
                    <div
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-neutral-200 py-12 dark:border-neutral-700">
                        <x-ui.icon name="document-text" class="mb-2 size-8 text-neutral-300 dark:text-neutral-600" />
                        <p class="text-sm text-neutral-400">Sin proposals aún</p>
                        <x-ui.button variant="outline" size="sm" :href="route('proposals.create')" wire:navigate class="mt-3">
                            Crear proposal
                        </x-ui.button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($client->proposals->sortByDesc('created_at') as $proposal)
                            @php
                                $statusColors = [
                                    'zinc' => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300',
                                    'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                    'green' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                    'red' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                                    'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                ];
                            @endphp
                            <a href="{{ route('proposals.show', $proposal) }}" wire:navigate
                                class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white p-4 transition-colors hover:border-[#FF420B]/30 hover:bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900 dark:hover:bg-neutral-800/50">
                                <div>
                                    <p class="font-mono text-xs text-[#FF420B]">{{ $proposal->number }}</p>
                                    <p class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ $proposal->title }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-400">{{ $proposal->project_type->label() }} ·
                                        {{ $proposal->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$proposal->status->color()] ?? $statusColors['zinc'] }}">
                                        {{ $proposal->status->label() }}
                                    </span>
                                    <p class="mt-2 font-mono text-sm font-bold text-neutral-900 dark:text-neutral-50">
                                        {{ $proposal->currency }} {{ number_format($proposal->total, 2) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Invoices section --}}
            <livewire:client.invoices.crud :client="$client" />

            {{-- Portal access section --}}
            <livewire:client.portal-users :client="$client" />

        </div>
    </div>
</div>
