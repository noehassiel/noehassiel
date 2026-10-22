<div>
    {{-- Page header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('proposals.index') }}" wire:navigate
                class="mb-1 inline-flex items-center gap-1.5 text-xs font-medium text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                <x-ui.icon name="arrow-left" class="size-3.5" />
                Todos los proposals
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $proposal->title }}</h1>
                @php
                    $colors = [
                        'zinc' => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
                        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        'green' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        'red' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                        'amber' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                    ];
                    $statusColor = $colors[$proposal->status->color()] ?? $colors['zinc'];
                @endphp
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                    {{ $proposal->status->label() }}
                </span>
            </div>
            <p class="mt-1 font-mono text-sm text-[#FF420B]">{{ $proposal->number }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Status change actions --}}
            @if ($proposal->status->value === 'draft')
                <x-ui.button variant="outline" size="sm" wire:click="markAsSent" icon="paper-airplane">
                    Marcar como enviado
                </x-ui.button>
            @elseif ($proposal->status->value === 'sent')
                <x-ui.button variant="outline" size="sm" wire:click="markAsAccepted" icon="check-circle"
                    class="text-green-600 border-green-200 hover:bg-green-50 dark:text-green-400 dark:border-green-800">
                    Aceptado
                </x-ui.button>
                <x-ui.button variant="outline" size="sm" wire:click="markAsRejected" icon="x-circle"
                    class="text-red-500 border-red-200 hover:bg-red-50 dark:text-red-400 dark:border-red-800">
                    Rechazado
                </x-ui.button>
            @endif

            {{-- Send options --}}
            <x-ui.dropdown>
                <x-slot:button>
                    <x-ui.button variant="outline" size="sm" icon="share" iconAfter="chevron-down">
                        Enviar
                    </x-ui.button>
                </x-slot:button>
                <x-slot:menu>
                    <x-ui.dropdown.item icon="envelope" wire:click="openSendModal">
                        Enviar por correo
                    </x-ui.dropdown.item>
                    <x-ui.dropdown.item icon="chat-bubble-left-ellipsis" href="{{ $this->getWhatsappLink() }}"
                        target="_blank">
                        Compartir por WhatsApp
                    </x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>

            <x-ui.button variant="outline" size="sm" wire:click="downloadPdf" icon="arrow-down-tray">
                Descargar PDF
            </x-ui.button>

            <x-ui.button variant="outline" size="sm" :href="route('proposals.edit', $proposal)" wire:navigate icon="pencil-square">
                Editar
            </x-ui.button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Scope --}}
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-neutral-400">Alcance del proyecto
                </h2>

                <div class="mb-5">
                    <p class="mb-1 text-xs font-semibold text-neutral-400">¿Qué vamos a hacer?</p>
                    <p class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
                        {{ $proposal->what_we_do }}</p>
                </div>

                <div>
                    <p class="mb-1 text-xs font-semibold text-neutral-400">¿Cómo lo vamos a hacer?</p>
                    <p class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
                        {{ $proposal->how_we_do_it }}</p>
                </div>
            </div>

            {{-- Includes / Excludes --}}
            @if ($proposal->includes || $proposal->excludes)
                <div class="grid gap-4 sm:grid-cols-2">
                    @if ($proposal->includes)
                        <div
                            class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <h3
                                class="mb-3 flex items-center gap-2 text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                <span
                                    class="flex size-5 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900/30">
                                    <x-ui.icon name="check" class="size-3" />
                                </span>
                                Incluye
                            </h3>
                            <ul class="space-y-1.5">
                                @foreach ($proposal->includes as $item)
                                    <li class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-green-400"></span>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($proposal->excludes)
                        <div
                            class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                            <h3
                                class="mb-3 flex items-center gap-2 text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                <span
                                    class="flex size-5 items-center justify-center rounded-full bg-red-100 text-red-500 dark:bg-red-900/30">
                                    <x-ui.icon name="x-mark" class="size-3" />
                                </span>
                                No incluye
                            </h3>
                            <ul class="space-y-1.5">
                                @foreach ($proposal->excludes as $item)
                                    <li class="flex items-start gap-2 text-sm text-neutral-600 dark:text-neutral-400">
                                        <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-red-400"></span>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Stages --}}
            @if ($proposal->stages->isNotEmpty())
                <div
                    class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-neutral-400">Etapas del proyecto
                    </h2>

                    <div class="space-y-4">
                        @foreach ($proposal->stages as $stage)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <span
                                        class="flex size-7 shrink-0 items-center justify-center rounded-full bg-[#FF420B]/10 text-xs font-bold text-[#FF420B]">
                                        {{ $loop->iteration }}
                                    </span>
                                    @if (!$loop->last)
                                        <div class="mt-2 w-px grow bg-neutral-200 dark:bg-neutral-700"></div>
                                    @endif
                                </div>
                                <div class="pb-4">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                            {{ $stage->name }}</h3>
                                        @if ($stage->duration)
                                            <span
                                                class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">{{ $stage->duration }}</span>
                                        @endif
                                    </div>
                                    @if ($stage->description)
                                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                            {{ $stage->description }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            @if ($proposal->notes)
                <div
                    class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-neutral-400">Notas y términos
                    </h2>
                    <p class="text-sm leading-relaxed whitespace-pre-line text-neutral-600 dark:text-neutral-400">
                        {{ $proposal->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: Meta + Cost --}}
        <div class="space-y-6">

            {{-- Meta info --}}
            <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-neutral-400">Detalles</h2>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-neutral-400">Cliente</dt>
                        <dd class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">
                            {{ $proposal->recipient()?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-400">Tipo de proyecto</dt>
                        <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">
                            {{ $proposal->project_type->label() }}</dd>
                    </div>
                    @if ($proposal->valid_until)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Válido hasta</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">
                                {{ $proposal->valid_until->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if ($proposal->sent_at)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Enviado</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">
                                {{ $proposal->sent_at->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if ($proposal->client_signed_at)
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Firmado por cliente</dt>
                            <dd class="mt-0.5 font-medium text-green-600 dark:text-green-400">
                                {{ $proposal->client_signature }} · {{ $proposal->client_signed_at->format('d/m/Y') }}
                            </dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-neutral-400">Creado</dt>
                        <dd class="mt-0.5 text-neutral-500">{{ $proposal->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Cost breakdown --}}
            @if ($proposal->items->isNotEmpty())
                <div
                    class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
                    <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-neutral-400">Inversión</h2>

                    <div class="space-y-2">
                        @foreach ($proposal->items as $item)
                            <div class="flex items-start justify-between gap-3 text-sm">
                                <span class="text-neutral-600 dark:text-neutral-400">{{ $item->description }}
                                    @if ($item->quantity != 1)
                                        <span class="text-xs text-neutral-400"> × {{ $item->quantity }}</span>
                                    @endif
                                </span>
                                <span class="shrink-0 font-mono font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ number_format($item->subtotal, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 space-y-2 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                        <div class="flex justify-between text-sm text-neutral-500">
                            <span>Subtotal</span>
                            <span class="font-mono">{{ $proposal->currency }}
                                {{ number_format($proposal->subtotal, 2) }}</span>
                        </div>
                        @if ($proposal->discount > 0)
                            <div class="flex justify-between text-sm text-neutral-500">
                                <span>Descuento</span>
                                <span class="font-mono">- {{ number_format($proposal->discount, 2) }}</span>
                            </div>
                        @endif
                        @if ($proposal->tax_rate > 0)
                            <div class="flex justify-between text-sm text-neutral-500">
                                <span>IVA ({{ $proposal->tax_rate }}%)</span>
                                <span
                                    class="font-mono">{{ number_format(($proposal->subtotal - $proposal->discount) * ($proposal->tax_rate / 100), 2) }}</span>
                            </div>
                        @endif
                        <div
                            class="flex justify-between rounded-xl bg-[#FF420B]/5 px-3 py-2.5 font-bold text-neutral-900 dark:text-neutral-50">
                            <span>Total</span>
                            <span class="font-mono text-[#FF420B]">
                                {{ $proposal->currency }} {{ number_format($proposal->total, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Send email modal --}}
    <x-ui.modal id="send-modal" heading="Enviar por correo">
        <div class="space-y-4">
            <x-ui.field>
                <x-ui.label>Correo del cliente *</x-ui.label>
                <x-ui.input wire:model="sendEmail" type="email" placeholder="cliente@ejemplo.com" />
                <x-ui.error name="sendEmail" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label>Mensaje personalizado <span class="text-neutral-400">(opcional)</span></x-ui.label>
                <x-ui.textarea wire:model="sendMessage" rows="3"
                    placeholder="Hola, te comparto el proposal para tu revisión..." />
            </x-ui.field>
        </div>

        <x-slot:footer>
            <x-ui.button variant="outline" wire:click="closeSendModal">Cancelar</x-ui.button>
            <x-ui.button variant="primary" wire:click="sendByEmail" wire:loading.attr="disabled"
                icon="paper-airplane">
                <x-ui.icon.loading class="size-4" wire:loading wire:target="sendByEmail" />
                <span wire:loading.remove wire:target="sendByEmail">Enviar correo</span>
                <span wire:loading wire:target="sendByEmail">Enviando...</span>
            </x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
