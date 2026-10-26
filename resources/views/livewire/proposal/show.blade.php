<div>
    {{-- Page header --}}
    @php
        $colors = [
            'zinc' => 'bg-neutral-800 text-neutral-300',
            'blue' => 'bg-blue-900/30 text-blue-300',
            'green' => 'bg-green-900/30 text-green-300',
            'red' => 'bg-red-900/30 text-red-300',
            'amber' => 'bg-amber-900/30 text-amber-300',
        ];
        $statusColor = $colors[$proposal->status->color()] ?? $colors['zinc'];
    @endphp
    <x-admin.page-header :back="route('proposals.index')" back-label="Todos los proposals" :title="$proposal->title">
        <x-slot:meta>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColor }}">
                {{ $proposal->status->label() }}
            </span>
            <span class="admin-chip text-[var(--ember)]">{{ $proposal->number }}</span>
        </x-slot:meta>
        <x-slot:actions>
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
                    @if ($proposal->isShared())
                        <x-ui.dropdown.item icon="chat-bubble-left-ellipsis" href="{{ $this->whatsappLink() }}"
                            target="_blank">
                            Compartir por WhatsApp
                        </x-ui.dropdown.item>
                    @else
                        <x-ui.dropdown.item icon="link" wire:click="enableSharing">
                            Crear link privado
                        </x-ui.dropdown.item>
                    @endif
                </x-slot:menu>
            </x-ui.dropdown>

            <x-ui.button variant="outline" size="sm" wire:click="downloadPdf" icon="arrow-down-tray">
                Descargar PDF
            </x-ui.button>

            <x-ui.button variant="outline" size="sm" :href="route('proposals.edit', $proposal)" wire:navigate icon="pencil-square">
                Editar
            </x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Main content --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Scope --}}
            <div class="admin-card p-6">
                <h2 class="mb-4 admin-eyebrow">Alcance del proyecto
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
                            class="admin-card p-6">
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
                            class="admin-card p-6">
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
                    class="admin-card p-6">
                    <h2 class="mb-4 admin-eyebrow">Etapas del proyecto
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
                    class="admin-card p-6">
                    <h2 class="mb-3 admin-eyebrow">Notas y términos
                    </h2>
                    <p class="text-sm leading-relaxed whitespace-pre-line text-neutral-600 dark:text-neutral-400">
                        {{ $proposal->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: Meta + Cost --}}
        <div class="space-y-6">

            {{-- Client answer and deposit --}}
            @if ($proposal->responded_at || $proposal->events->isNotEmpty() || $proposal->isDepositPaid() || $proposal->status === \App\Enums\ProposalStatus::Accepted)
                <div class="admin-card p-6">
                    <h2 class="mb-4 admin-eyebrow">Respuesta del cliente</h2>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Estado</dt>
                            <dd class="mt-0.5 font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ $proposal->status->label() }}
                                @if ($proposal->responded_at)
                                    <span class="font-normal text-neutral-500">· {{ $proposal->responded_at->diffForHumans() }}</span>
                                @endif
                            </dd>
                        </div>
                        @if ($proposal->client_response)
                            <div>
                                <dt class="text-xs font-medium text-neutral-400">{{ $proposal->status === \App\Enums\ProposalStatus::Rejected ? 'Motivo' : 'Cambios que pidió' }}</dt>
                                <dd class="mt-1 whitespace-pre-line rounded-lg bg-neutral-50 p-3 text-neutral-700 dark:bg-neutral-800/60 dark:text-neutral-300">{{ $proposal->client_response }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Factura</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">{{ $proposal->requires_invoice ? 'Sí, requiere factura' : 'No' }}</dd>
                        </div>
                        @if ($proposal->payment_method)
                            <div>
                                <dt class="text-xs font-medium text-neutral-400">Forma de pago</dt>
                                <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">{{ $proposal->payment_method->label() }}</dd>
                            </div>
                        @endif
                        @if ($proposal->isDepositPaid())
                            <div>
                                <dt class="text-xs font-medium text-neutral-400">Anticipo</dt>
                                <dd class="mt-0.5 font-medium text-green-600 dark:text-green-400">
                                    {{ number_format($proposal->deposit_paid_amount, 2) }} · {{ $proposal->deposit_paid_at->format('d/m/Y') }}
                                </dd>
                            </div>
                        @endif
                    </dl>

                    @if ($proposal->events->isNotEmpty())
                        <h3 class="mt-5 mb-2 text-xs font-medium text-neutral-400">Historial</h3>
                        <ol class="space-y-2 text-sm">
                            @foreach ($proposal->events as $event)
                                <li class="border-l-2 pl-3 {{ $event->type->isFromClient() ? 'border-neutral-300 dark:border-neutral-600' : 'border-[#FF420B]' }}">
                                    <p class="font-medium text-neutral-700 dark:text-neutral-300">{{ $event->type->adminLabel() }}
                                        <span class="font-normal text-neutral-400">· {{ $event->created_at->translatedFormat('j M, H:i') }}</span></p>
                                    @if ($event->body)
                                        <p class="mt-0.5 whitespace-pre-line text-neutral-500">{{ $event->body }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif

                    @if ($proposal->project_id)
                        <x-ui.button variant="outline" size="sm" class="mt-4" :href="route('projects.show', $proposal->project_id)" wire:navigate icon="arrow-right">Ver proyecto</x-ui.button>

                        @if ($proposal->balance_paid_at)
                            <p class="mt-4 text-sm font-medium text-green-600 dark:text-green-400">
                                Liquidación de {{ number_format($proposal->balance_paid_amount, 2) }} recibida el {{ $proposal->balance_paid_at->format('d/m/Y') }} · {{ $proposal->balance_payment_method->label() }}
                            </p>
                        @else
                            <p class="mt-4 mb-2 text-xs text-neutral-400">Al entregar el proyecto: liquidación de {{ number_format($proposal->balanceAmount(), 2) }}.</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                    <x-ui.button variant="outline" size="sm" wire:click="markBalanceReceived('{{ $method->value }}')"
                                        wire:confirm="¿Confirmas que recibiste la liquidación por {{ Str::lower($method->label()) }}?">
                                        Liquidación por {{ Str::lower($method->label()) }}
                                    </x-ui.button>
                                @endforeach
                            </div>
                        @endif
                    @elseif ($proposal->status === \App\Enums\ProposalStatus::Accepted)
                        <p class="mt-4 mb-2 text-xs text-neutral-400">¿Ya recibiste el anticipo de {{ number_format($proposal->depositAmount(), 2) }}? Al marcarlo se crea el proyecto y se le manda la bienvenida.</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([\App\Enums\PaymentMethod::Transfer, \App\Enums\PaymentMethod::Cash] as $method)
                                <x-ui.button variant="outline" size="sm" wire:click="markDepositReceived('{{ $method->value }}')"
                                    wire:confirm="¿Confirmas que recibiste el anticipo por {{ Str::lower($method->label()) }}? Se creará el proyecto y se avisará al cliente.">
                                    Recibido por {{ Str::lower($method->label()) }}
                                </x-ui.button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Meta info --}}
            <div class="admin-card p-6">
                <h2 class="mb-4 admin-eyebrow">Detalles</h2>

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

            {{-- Private client link --}}
            <div class="admin-card p-6">
                <h2 class="mb-4 admin-eyebrow">Link para el cliente</h2>

                @if ($proposal->isShared())
                    <div x-data="{ copied: null, copy(value, key) { navigator.clipboard.writeText(value); this.copied = key; setTimeout(() => this.copied = null, 1500) } }">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">URL</dt>
                            <dd class="mt-0.5 flex items-center gap-2">
                                <a href="{{ $proposal->publicUrl() }}" target="_blank" class="min-w-0 truncate font-mono text-xs text-neutral-700 hover:underline dark:text-neutral-300">{{ $proposal->publicUrl() }}</a>
                                <button type="button" @click="copy(@js($proposal->publicUrl()), 'url')" class="shrink-0 cursor-pointer text-xs font-medium text-[#FF420B]"
                                    x-text="copied === 'url' ? 'Copiado' : 'Copiar'"></button>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Contraseña</dt>
                            <dd class="mt-0.5 flex items-center gap-2">
                                <span class="font-mono text-neutral-800 dark:text-neutral-200">{{ $proposal->access_password }}</span>
                                <button type="button" @click="copy(@js($proposal->access_password), 'password')" class="shrink-0 cursor-pointer text-xs font-medium text-[#FF420B]"
                                    x-text="copied === 'password' ? 'Copiada' : 'Copiar'"></button>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-neutral-400">Vistas</dt>
                            <dd class="mt-0.5 text-neutral-700 dark:text-neutral-300">
                                @if ($proposal->first_viewed_at)
                                    {{ $proposal->view_count }} · última {{ $proposal->last_viewed_at->diffForHumans() }}
                                @else
                                    Aún no la abre
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <p class="mt-4 text-xs text-neutral-400">El mensaje lleva link y contraseña juntos, listo para WhatsApp, iMessage o correo. Si prefieres más seguridad, manda la contraseña por otro canal.</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-ui.button variant="primary" size="sm" icon="clipboard-document" @click="copy(@js($proposal->shareMessage()), 'message')">
                            <span x-text="copied === 'message' ? 'Mensaje copiado' : 'Copiar mensaje'"></span>
                        </x-ui.button>
                        <x-ui.button variant="outline" size="sm" wire:click="rotateAccessPassword"
                            wire:confirm="La contraseña actual dejará de funcionar. ¿Continuar?">Nueva contraseña</x-ui.button>
                        <x-ui.button variant="outline" size="sm" wire:click="disableSharing"
                            wire:confirm="El cliente ya no podrá abrir el link. ¿Desactivar?"
                            class="text-red-500 border-red-200 hover:bg-red-50 dark:text-red-400 dark:border-red-800">Desactivar</x-ui.button>
                    </div>
                    </div>
                @else
                    <p class="mb-4 text-sm text-neutral-500">Crea un link privado con contraseña para que el cliente vea la propuesta en línea.</p>
                    <x-ui.button variant="primary" size="sm" wire:click="enableSharing" icon="link">Crear link privado</x-ui.button>
                @endif
            </div>

            {{-- Cost breakdown --}}
            @if ($proposal->items->isNotEmpty())
                <div
                    class="admin-card p-6">
                    <h2 class="mb-4 admin-eyebrow">Inversión</h2>

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
                        @foreach ($proposal->paymentSchedule() as $payment)
                            <div @class([
                                'flex justify-between gap-3 text-xs',
                                'text-neutral-500' => ! $payment['paid_at'],
                                'font-medium text-green-600 dark:text-green-400' => $payment['paid_at'],
                            ])>
                                <span>{{ $payment['label'] }} {{ $payment['percent'] }}%@if ($payment['paid_at']) · pagado {{ $payment['paid_at']->format('d/m/Y') }}@endif</span>
                                <span class="font-mono">{{ number_format($payment['amount'], 2) }}</span>
                            </div>
                        @endforeach
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
