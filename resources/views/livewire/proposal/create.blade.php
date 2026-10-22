<div>
    <form wire:submit="save" class="mx-auto max-w-4xl space-y-8 pb-16">

        {{-- Page header --}}
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('proposals.index') }}" wire:navigate
                    class="mb-1 inline-flex items-center gap-1.5 text-xs font-medium text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                    <x-ui.icon name="arrow-left" class="size-3.5" />
                    Volver a proposals
                </a>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">Nuevo Proposal</h1>
            </div>
            <x-ui.button type="submit" variant="primary" icon="ps:check">
                Guardar proposal
            </x-ui.button>
        </div>

        {{-- Section: Basic info --}}
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="mb-5 text-sm font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                Información general</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field class="sm:col-span-2">
                    <x-ui.label>Cliente o lead *</x-ui.label>
                    <x-ui.select wire:model="recipient">
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
                    <x-ui.input wire:model="title" placeholder="Ej. Sitio web corporativo para Grupo Innovar" />
                    <x-ui.error name="title" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Tipo de proyecto *</x-ui.label>
                    <x-ui.select wire:model="projectType">
                        @foreach ($this->projectTypes as $type)
                            <x-ui.select.option value="{{ $type->value }}">{{ $type->label() }}</x-ui.select.option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.error name="projectType" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Válido hasta</x-ui.label>
                    <x-ui.input type="date" wire:model="validUntil" />
                    <x-ui.error name="validUntil" />
                </x-ui.field>
            </div>
        </div>

        {{-- Section: Scope --}}
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="mb-5 text-sm font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                Alcance del proyecto</h2>

            <div class="space-y-4">
                <x-ui.field>
                    <x-ui.label>¿Qué vamos a hacer? *</x-ui.label>
                    <x-ui.textarea wire:model="whatWeDo" rows="4"
                        placeholder="Describe qué se va a construir, diseñar o desarrollar..." />
                    <x-ui.error name="whatWeDo" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>¿Cómo lo vamos a hacer? *</x-ui.label>
                    <x-ui.textarea wire:model="howWeDoIt" rows="4"
                        placeholder="Describe el proceso, metodología y tecnologías que se usarán..." />
                    <x-ui.error name="howWeDoIt" />
                </x-ui.field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field>
                        <x-ui.label>¿Qué incluye?</x-ui.label>
                        <x-ui.textarea wire:model="includesRaw" rows="5"
                            placeholder="Un item por línea:&#10;Diseño UI/UX&#10;Desarrollo frontend&#10;Hosting primer año" />
                        <x-ui.text class="mt-1.5 text-xs text-neutral-400">Un ítem por línea</x-ui.text>
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>¿Qué no incluye? <span class="text-neutral-400">(opcional)</span></x-ui.label>
                        <x-ui.textarea wire:model="excludesRaw" rows="5"
                            placeholder="Un item por línea:&#10;Creación de contenido&#10;Fotografía" />
                        <x-ui.text class="mt-1.5 text-xs text-neutral-400">Un ítem por línea</x-ui.text>
                    </x-ui.field>
                </div>
            </div>
        </div>

        {{-- Section: Stages --}}
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Etapas
                    del proyecto</h2>
                <x-ui.button type="button" variant="outline" size="sm" wire:click="addStage" icon="ps:plus">
                    Agregar etapa
                </x-ui.button>
            </div>

            <div class="space-y-4">
                @foreach ($stages as $i => $stage)
                    <div wire:key="stage-{{ $i }}"
                        class="group relative rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                        <div class="mb-3 flex items-center gap-3">
                            <span
                                class="flex size-6 shrink-0 items-center justify-center rounded-full bg-[#FF420B] text-xs font-bold text-white">{{ $i + 1 }}</span>
                            <x-ui.input wire:model="stages.{{ $i }}.name"
                                placeholder="Nombre de la etapa (ej. Diseño UI/UX)" class="flex-1" />
                            @if (count($stages) > 1)
                                <button type="button" wire:click="removeStage({{ $i }})"
                                    class="shrink-0 p-1 text-neutral-300 hover:text-red-500 dark:text-neutral-600">
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
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    Desglose de costos</h2>
                <x-ui.button type="button" variant="outline" size="sm" wire:click="addItem" icon="ps:plus">
                    Agregar ítem
                </x-ui.button>
            </div>

            <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-400">Descripción</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 w-24">Cant.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 w-36">Precio unit.
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-400 w-36">Subtotal</th>
                            <th class="w-10 px-2 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach ($items as $i => $item)
                            <tr wire:key="item-{{ $i }}">
                                <td class="px-4 py-2.5">
                                    <x-ui.input wire:model="items.{{ $i }}.description"
                                        placeholder="Descripción del servicio o entregable" />
                                    <x-ui.error name="items.{{ $i }}.description" />
                                </td>
                                <td class="px-4 py-2.5 w-24">
                                    <x-ui.input type="number" wire:model.live="items.{{ $i }}.quantity"
                                        min="0.01" step="0.01" class="text-right" />
                                </td>
                                <td class="px-4 py-2.5 w-36">
                                    <x-ui.input type="number" wire:model.live="items.{{ $i }}.unit_price"
                                        min="0" step="0.01" class="text-right" />
                                </td>
                                <td
                                    class="px-4 py-2.5 w-36 text-right font-mono text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                    {{ number_format($item['subtotal'] ?? 0, 2) }}
                                </td>
                                <td class="px-2 py-2.5 text-center">
                                    @if (count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $i }})"
                                            class="p-1 text-neutral-300 hover:text-red-500 dark:text-neutral-600">
                                            <x-ui.icon name="x-mark" class="size-4" />
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="mt-4 flex justify-end">
                <div class="w-full max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between text-neutral-500">
                        <span>Subtotal</span>
                        <span class="font-mono font-medium text-neutral-700 dark:text-neutral-300">
                            {{ $currency }} {{ number_format($this->subtotal, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-neutral-500">
                        <span>Descuento</span>
                        <div class="flex items-center gap-2">
                            <x-ui.input type="number" wire:model.live="discount" min="0" step="0.01"
                                class="w-28 text-right text-xs" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-neutral-500">
                        <label class="flex cursor-pointer items-center gap-2">
                            <x-ui.checkbox wire:model.live="applyTax" />
                            <span>IVA (%)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            @if ($applyTax)
                                <x-ui.input type="number" wire:model.live="taxRate" min="0" max="100"
                                    step="0.01" class="w-20 text-right text-xs" />
                            @else
                                <span class="font-mono text-xs text-neutral-400">Sin IVA</span>
                            @endif
                        </div>
                    </div>

                    <div
                        class="flex justify-between border-t border-neutral-200 pt-2 font-semibold text-neutral-900 dark:border-neutral-700 dark:text-neutral-50">
                        <span>Total</span>
                        <span class="font-mono text-[#FF420B]">
                            {{ $currency }} {{ number_format($this->total, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Notes --}}
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="mb-5 text-sm font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                Notas y términos adicionales</h2>
            <x-ui.textarea wire:model="notes" rows="4"
                placeholder="Condiciones de pago, términos especiales, observaciones..." />
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3">
            <x-ui.button type="button" variant="outline" :href="route('proposals.index')" wire:navigate>
                Cancelar
            </x-ui.button>
            <x-ui.button type="submit" variant="primary" icon="ps:check">
                Guardar proposal
            </x-ui.button>
        </div>

    </form>
</div>
