<div>
    <form wire:submit="save" class="mx-auto max-w-4xl space-y-8 pb-16">

        {{-- Page header --}}
        <x-admin.page-header class="!mb-0" :back="route('proposals.show', $proposal)" back-label="Volver al proposal" title="Editar proposal">
            <x-slot:meta>
                <span class="admin-chip text-[var(--ember)]">{{ $proposal->number }}</span>
            </x-slot:meta>
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="check">
                    Guardar cambios
                </x-ui.button>
            </x-slot:actions>
        </x-admin.page-header>

        {{-- What the client asked for, so the edit answers it --}}
        @if ($proposal->status === \App\Enums\ProposalStatus::ChangesRequested && $proposal->client_response)
            <div role="status" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-900/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                    El cliente pidió cambios · {{ $proposal->responded_at?->translatedFormat('j M, H:i') }}
                </p>
                <p class="mt-2 whitespace-pre-line text-amber-950 dark:text-amber-100">{{ $proposal->client_response }}</p>
            </div>
        @endif

        {{-- Section: AI editor --}}
        <div class="admin-card p-6">
            <h2 class="mb-2 admin-eyebrow">Cambios con IA</h2>
            <p class="mb-5 text-sm text-neutral-500">Dile qué cambiar: texto, alcance, etapas, conceptos o precios. Revisa el resultado abajo y guarda.</p>

            @if ($aiHistory)
                <ol class="mb-4 space-y-3 text-sm" aria-label="Cambios en esta sesión">
                    @foreach ($aiHistory as $message)
                        <li @class([
                            'rounded-xl px-4 py-3 whitespace-pre-line',
                            'ml-8 bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' => $message['role'] === 'user',
                            'mr-8 border border-neutral-200 text-neutral-600 dark:border-neutral-800 dark:text-neutral-400' => $message['role'] === 'assistant',
                        ])>{{ $message['content'] }}</li>
                    @endforeach
                </ol>
            @endif

            <x-ui.field>
                <x-ui.label class="sr-only">Cambio a aplicar</x-ui.label>
                <x-ui.textarea wire:model="aiInstruction" rows="3" wire:keydown.meta.enter.prevent="editWithAi"
                    placeholder="Ej. Quita el blog, agrega una etapa de pruebas de 1 semana y baja el total a 45 mil." />
                <x-ui.error name="aiInstruction" />
            </x-ui.field>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-ui.button type="button" variant="outline" wire:click="editWithAi" wire:loading.attr="disabled" wire:target="editWithAi" icon="sparkles">
                    <span wire:loading.remove wire:target="editWithAi">Aplicar cambios</span>
                    <span wire:loading wire:target="editWithAi">Aplicando…</span>
                </x-ui.button>
                @if ($aiUndoSnapshot)
                    <x-ui.button type="button" variant="ghost" wire:click="undoAiEdit" icon="arrow-uturn-left">Deshacer</x-ui.button>
                @endif
                <span class="text-xs text-neutral-400">⌘ + Enter</span>
            </div>
        </div>

        {{-- Section: Basic info --}}
        <div class="admin-card p-6">
            <h2 class="mb-5 admin-eyebrow">
                Información general</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Cliente o lead *</x-ui.label>
                    <x-ui.select wire:model="recipient" placeholder="Selecciona un cliente o lead..." searchable>
                        @foreach ($this->clients as $client)
                            <x-ui.select.option value="client:{{ $client->id }}">{{ $client->name }}</x-ui.select.option>
                        @endforeach
                        @foreach ($this->leads as $lead)
                            <x-ui.select.option value="lead:{{ $lead->id }}">{{ $lead->name }}{{ $lead->company ? " · {$lead->company}" : '' }} (lead)</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.error name="recipient" />
                </x-ui.field>

                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Título del proyecto *</x-ui.label>
                    <x-ui.input wire:model="title" placeholder="Ej. Sitio web corporativo" />
                    <x-ui.error name="title" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Tipo de proyecto *</x-ui.label>
                    <x-ui.select wire:model="projectType" placeholder="Selecciona...">
                        @foreach ($this->projectTypes as $type)
                            <x-ui.select.option value="{{ $type->value }}">{{ $type->label() }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.error name="projectType" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Válido hasta</x-ui.label>
                    <x-ui.date-picker wire:model="validUntil" label="Selecciona una fecha" locale="es-MX" clearable />
                </x-ui.field>
            </div>
        </div>

        {{-- Section: Scope --}}
        <div class="admin-card p-6">
            <h2 class="mb-5 admin-eyebrow">
                Alcance del proyecto</h2>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>¿Qué vamos a hacer? *</x-ui.label>
                    <x-ui.textarea wire:model="whatWeDo" rows="4" />
                    <x-ui.error name="whatWeDo" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>¿Cómo lo vamos a hacer? *</x-ui.label>
                    <x-ui.textarea wire:model="howWeDoIt" rows="4" />
                    <x-ui.error name="howWeDoIt" />
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field>
                        <x-ui.label>¿Qué incluye?</x-ui.label>
                        <x-ui.textarea wire:model="includesRaw" rows="5" placeholder="Un item por línea" />
                        <x-ui.text class="mt-1.5 text-xs text-neutral-400">Un ítem por línea</x-ui.text>
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>¿Qué no incluye? <span class="text-neutral-400">(opcional)</span></x-ui.label>
                        <x-ui.textarea wire:model="excludesRaw" rows="5" placeholder="Un item por línea" />
                        <x-ui.text class="mt-1.5 text-xs text-neutral-400">Un ítem por línea</x-ui.text>
                    </x-ui.field>
                </div>
            </div>
        </div>

        {{-- Section: Stages --}}
        <div class="admin-card p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="admin-eyebrow">Etapas
                    del proyecto</h2>
                <x-ui.button type="button" variant="outline" size="sm" wire:click="addStage" icon="plus">
                    Agregar etapa
                </x-ui.button>
            </div>

            <div class="space-y-4">
                @foreach ($stages as $i => $stage)
                    <div wire:key="stage-{{ $i }}"
                        class="group relative admin-subcard p-4">
                        <div class="mb-3 flex items-center gap-3">
                            <span
                                class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#FF420B] text-xs font-bold text-white">{{ $i + 1 }}</span>
                            <x-ui.input wire:model="stages.{{ $i }}.name" placeholder="Nombre de la etapa"
                                class="flex-1" />
                            @if (count($stages) > 1)
                                <button type="button" wire:click="removeStage({{ $i }})"
                                    class="shrink-0 p-1 text-neutral-300 hover:text-red-500">
                                    <x-ui.icon name="x-mark" class="size-4" />
                                </button>
                            @endif
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <x-ui.input wire:model="stages.{{ $i }}.description"
                                placeholder="Descripción breve" class="sm:col-span-2" />
                            <x-ui.input wire:model="stages.{{ $i }}.duration"
                                placeholder="Duración (ej. 2 semanas)" />
                        </div>
                        <x-ui.error name="stages.{{ $i }}.name" />
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section: Cost items --}}
        <div class="admin-card p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="admin-eyebrow">
                    Desglose de costos</h2>
                <x-ui.button type="button" variant="outline" size="sm" wire:click="addItem" icon="plus">
                    Agregar ítem
                </x-ui.button>
            </div>

            <div class="admin-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400">Descripción</th>
                            <th class="w-24 px-4 py-3 text-right text-xs font-semibold text-neutral-400">Cant.</th>
                            <th class="w-36 px-4 py-3 text-right text-xs font-semibold text-neutral-400">Precio unit.
                            </th>
                            <th class="w-36 px-4 py-3 text-right text-xs font-semibold text-neutral-400">Subtotal</th>
                            <th class="w-10 px-2 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @foreach ($items as $i => $item)
                            <tr wire:key="item-{{ $i }}">
                                <td class="px-4 py-2.5">
                                    <x-ui.input wire:model="items.{{ $i }}.description"
                                        placeholder="Descripción del servicio" />
                                    <x-ui.error name="items.{{ $i }}.description" />
                                </td>
                                <td class="w-24 px-4 py-2.5">
                                    <x-ui.input type="number" wire:model.live="items.{{ $i }}.quantity"
                                        min="0.01" step="0.01" class="text-right" />
                                </td>
                                <td class="w-36 px-4 py-2.5">
                                    <x-ui.input type="number" wire:model.live="items.{{ $i }}.unit_price"
                                        min="0" step="0.01" class="text-right" />
                                </td>
                                <td
                                    class="w-36 px-4 py-2.5 text-right font-mono text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                    {{ number_format($item['subtotal'] ?? 0, 2) }}
                                </td>
                                <td class="px-2 py-2.5 text-center">
                                    @if (count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $i }})"
                                            class="p-1 text-neutral-300 hover:text-red-500">
                                            <x-ui.icon name="x-mark" class="size-4" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex justify-end">
                <div class="w-full max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between text-neutral-500">
                        <span>Subtotal</span>
                        <span class="font-mono font-medium text-neutral-700 dark:text-neutral-300">{{ $currency }}
                            {{ number_format($this->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-neutral-500">
                        <span>Descuento</span>
                        <x-ui.input type="number" wire:model.live="discount" min="0" step="0.01"
                            class="w-28 text-right text-xs" />
                    </div>
                    <div class="flex items-center justify-between text-neutral-500">
                        <label class="flex cursor-pointer items-center gap-2">
                            <x-ui.checkbox wire:model.live="applyTax" />
                            <span>IVA (%)</span>
                        </label>
                        @if ($applyTax)
                            <x-ui.input type="number" wire:model.live="taxRate" min="0" max="100"
                                step="0.01" class="w-20 text-right text-xs" />
                        @else
                            <span class="font-mono text-xs text-neutral-400">Sin IVA</span>
                        @endif
                    </div>
                    <div
                        class="flex justify-between border-t border-neutral-200 pt-2 font-semibold text-neutral-900 dark:border-neutral-700 dark:text-neutral-50">
                        <span>Total</span>
                        <span class="font-mono text-[#FF420B]">{{ $currency }}
                            {{ number_format($this->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Update note for the client --}}
        @if ($proposal->isShared())
            <div class="admin-card p-6">
                <h2 class="mb-2 admin-eyebrow">Nota de esta actualización</h2>
                <p class="mb-4 text-sm text-neutral-500">El cliente la verá en el historial de su propuesta, con la fecha. Déjala vacía si solo corregiste detalles.</p>
                <x-ui.textarea wire:model="updateNote" rows="3" placeholder="Ej. Bajé el precio del diseño y quité el blog, como lo platicamos." />
                <x-ui.error name="updateNote" />
            </div>
        @endif

        {{-- Notes --}}
        <div class="admin-card p-6">
            <h2 class="mb-5 admin-eyebrow">
                Notas y términos adicionales</h2>
            <x-ui.textarea wire:model="notes" rows="4" />
        </div>

        <div class="flex justify-end gap-3">
            <x-ui.button type="button" variant="outline" :href="route('proposals.show', $proposal)" wire:navigate>Cancelar</x-ui.button>
            <x-ui.button type="submit" variant="primary" icon="check">
                Guardar cambios
            </x-ui.button>
        </div>

    </form>
</div>
