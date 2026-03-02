<div class="space-y-4">

    {{-- Flash message --}}
    @if (session()->has('success'))
        <div class="flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
            <x-ui.icon name="check-circle" class="size-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-neutral-400">Facturas</h2>

        <x-ui.button wire:click="openModal" variant="outline" size="sm" icon="paper-airplane">
            Enviar factura
        </x-ui.button>
    </div>

    {{-- Invoice list --}}
    @if ($invoices->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-neutral-200 py-10 dark:border-neutral-700">
            <x-ui.icon name="document-text" class="mb-2 size-7 text-neutral-300 dark:text-neutral-600" />
            <p class="text-sm text-neutral-400">Sin facturas enviadas</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($invoices as $invoice)
                <div wire:key="invoice-{{ $invoice->id }}"
                    class="flex items-center justify-between rounded-xl border border-neutral-200 bg-white px-4 py-3 dark:border-neutral-800 dark:bg-neutral-900">
                    <div>
                        <p class="font-mono text-xs text-[#FF420B]">{{ $invoice->number }}</p>
                        <p class="mt-0.5 text-sm text-neutral-700 dark:text-neutral-300">{{ $invoice->notes }}</p>
                        <p class="mt-0.5 text-xs text-neutral-400">{{ $invoice->issue_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        @if ($invoice->sent_at)
                            <x-ui.badge color="green" variant="soft" size="sm">Enviada</x-ui.badge>
                        @else
                            <x-ui.badge color="amber" variant="soft" size="sm">Pendiente</x-ui.badge>
                        @endif
                        <p class="mt-1.5 font-mono text-sm font-bold text-neutral-900 dark:text-neutral-50">
                            {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Create & Send Modal --}}
    <x-ui.modal id="invoice-modal" heading="Enviar factura" description="Completa los datos y sube el ZIP con PDF + XML." width="lg">

        <form wire:submit="sendInvoice" class="space-y-5">

            <div class="grid grid-cols-2 gap-4">
                <x-ui.field>
                    <x-ui.label>Número de factura</x-ui.label>
                    <x-ui.input wire:model="number" placeholder="F-001" left-icon="hashtag" />
                    <x-ui.error name="number" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Fecha de emisión</x-ui.label>
                    <x-ui.input wire:model="issue_date" type="date" />
                    <x-ui.error name="issue_date" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label>Concepto</x-ui.label>
                <x-ui.textarea wire:model="notes" placeholder="Ej: Desarrollo de sitio web corporativo" rows="2" />
                <x-ui.error name="notes" />
            </x-ui.field>

            <div class="grid grid-cols-3 gap-4">
                <x-ui.field>
                    <x-ui.label>Moneda</x-ui.label>
                    <x-ui.select wire:model="currency">
                        <x-ui.select.option value="MXN">MXN</x-ui.select.option>
                        <x-ui.select.option value="USD">USD</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="currency" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>IVA</x-ui.label>
                    <x-ui.input wire:model="tax" type="number" step="0.01" min="0" placeholder="0.00" left-icon="receipt-percent" />
                    <x-ui.error name="tax" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label>Total</x-ui.label>
                    <x-ui.input wire:model="total" type="number" step="0.01" min="0" placeholder="0.00" left-icon="banknotes" />
                    <x-ui.error name="total" />
                </x-ui.field>
            </div>

            {{-- ZIP upload --}}
            <x-ui.field>
                <x-ui.label>Archivo ZIP (PDF + XML)</x-ui.label>
                <div
                    x-data="{
                        dragging: false,
                        fileName: null,
                        handleDrop(e) {
                            this.dragging = false;
                            const file = e.dataTransfer.files[0];
                            if (file) {
                                this.fileName = file.name;
                                $wire.upload('zip', file);
                            }
                        },
                        handleChange(e) {
                            const file = e.target.files[0];
                            if (file) { this.fileName = file.name; }
                        }
                    }"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="handleDrop($event)"
                    :class="dragging ? 'border-[#FF420B] bg-[#FF420B]/5' : 'border-neutral-200 dark:border-neutral-700'"
                    class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 transition-colors"
                >
                    <x-ui.icon name="archive-box" class="mb-2 size-8 text-neutral-400" />

                    <template x-if="fileName">
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300" x-text="fileName"></p>
                    </template>

                    <template x-if="!fileName">
                        <div class="text-center">
                            <p class="text-sm text-neutral-500">Arrastra el ZIP aquí o</p>
                            <label class="mt-1 cursor-pointer text-sm font-medium text-[#FF420B] hover:underline">
                                selecciona un archivo
                                <input type="file" wire:model="zip" accept=".zip" class="sr-only" x-on:change="handleChange($event)" />
                            </label>
                        </div>
                    </template>

                    <div wire:loading wire:target="zip" class="mt-2 text-xs text-neutral-400">
                        Subiendo...
                    </div>
                </div>
                <x-ui.error name="zip" />
            </x-ui.field>

        </form>

        <x-slot:footer>
            <x-ui.button variant="outline" wire:click="closeModal">
                Cancelar
            </x-ui.button>
            <x-ui.button
                variant="primary"
                wire:click="sendInvoice"
                wire:loading.attr="disabled"
                wire:target="sendInvoice"
                icon="paper-airplane"
            >
                <span wire:loading.remove wire:target="sendInvoice">Enviar factura</span>
                <span wire:loading wire:target="sendInvoice">Enviando...</span>
            </x-ui.button>
        </x-slot:footer>

    </x-ui.modal>

</div>
