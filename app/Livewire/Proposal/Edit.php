<?php

namespace App\Livewire\Proposal;

use App\Ai\Agents\ProposalEditor;
use App\Enums\ProposalProjectType;
use App\Livewire\Proposal\Concerns\SelectsRecipient;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class Edit extends Component
{
    use SelectsRecipient;

    public Proposal $proposal;

    public string $title = '';

    public string $projectType = '';

    public string $whatWeDo = '';

    public string $howWeDoIt = '';

    public string $notes = '';

    public string $currency = 'MXN';

    public string $validUntil = '';

    public string $includesRaw = '';

    public string $excludesRaw = '';

    public array $stages = [];

    public array $items = [];

    public float $discount = 0;

    public float $taxRate = 16;

    public bool $applyTax = true;

    // AI editor
    public string $aiInstruction = '';

    /** @var array<int, array{role: 'user'|'assistant', content: string}> */
    public array $aiHistory = [];

    /** Form state before the last AI edit, so it can be undone. */
    public ?array $aiUndoSnapshot = null;

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal->load(['stages', 'items']);

        $this->recipient = $proposal->lead_id ? "lead:{$proposal->lead_id}" : "client:{$proposal->client_id}";
        $this->title = $proposal->title;
        $this->projectType = $proposal->project_type->value;
        $this->whatWeDo = $proposal->what_we_do;
        $this->howWeDoIt = $proposal->how_we_do_it;
        $this->notes = $proposal->notes ?? '';
        $this->currency = $proposal->currency;
        $this->validUntil = $proposal->valid_until?->format('Y-m-d') ?? '';
        $this->includesRaw = implode("\n", $proposal->includes ?? []);
        $this->excludesRaw = implode("\n", $proposal->excludes ?? []);
        $this->discount = (float) $proposal->discount;
        $this->taxRate = $proposal->tax_rate > 0 ? (float) $proposal->tax_rate : 16;
        $this->applyTax = $proposal->tax_rate > 0;

        $this->stages = $proposal->stages->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'description' => $s->description ?? '',
            'duration' => $s->duration ?? '',
            'order' => $s->order,
        ])->toArray();

        $this->items = $proposal->items->map(fn ($i) => [
            'id' => $i->id,
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'subtotal' => (float) $i->subtotal,
            'order' => $i->order,
        ])->toArray();
    }

    #[Computed]
    public function projectTypes(): array
    {
        return ProposalProjectType::cases();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) ($i['subtotal'] ?? 0));
    }

    #[Computed]
    public function tax(): float
    {
        if (! $this->applyTax) {
            return 0;
        }

        return ($this->subtotal - $this->discount) * ($this->taxRate / 100);
    }

    #[Computed]
    public function total(): float
    {
        return max(0, ($this->subtotal - $this->discount) + $this->tax);
    }

    public function addStage(): void
    {
        $this->stages[] = ['name' => '', 'description' => '', 'duration' => '', 'order' => count($this->stages)];
    }

    public function removeStage(int $index): void
    {
        unset($this->stages[$index]);
        $this->stages = array_values($this->stages);
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0, 'subtotal' => 0, 'order' => count($this->items)];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedItems(mixed $value, string $key): void
    {
        [$index, $field] = explode('.', $key, 2);

        if (in_array($field, ['quantity', 'unit_price'])) {
            $qty = (float) ($this->items[(int) $index]['quantity'] ?? 1);
            $price = (float) ($this->items[(int) $index]['unit_price'] ?? 0);
            $this->items[(int) $index]['subtotal'] = round($qty * $price, 2);
        }
    }

    /**
     * Apply a plain-language change request to the whole form. Nothing is saved until "Guardar cambios".
     */
    public function editWithAi(): void
    {
        $this->validate(['aiInstruction' => 'required|string|max:5000']);

        $snapshot = $this->formSnapshot();
        $prompt = 'Today: '.now()->toDateString()."\nClient: ".($this->proposal->recipient()?->name ?? '-')
            ."\n\nCurrent proposal:\n".json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ."\n\nChange request:\n{$this->aiInstruction}";

        try {
            $edited = (new ProposalEditor($this->aiHistory))->prompt($prompt);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('aiInstruction', 'No se pudieron aplicar los cambios. Intenta de nuevo.');

            return;
        }

        $this->aiUndoSnapshot = $snapshot;
        $this->applySnapshot($edited->toArray());

        // Only the request and the summary are kept, the proposal JSON is resent fresh each time
        $this->aiHistory = array_slice([
            ...$this->aiHistory,
            ['role' => 'user', 'content' => $this->aiInstruction],
            ['role' => 'assistant', 'content' => $edited['summary']],
        ], -10);

        $this->aiInstruction = '';
    }

    public function undoAiEdit(): void
    {
        if ($this->aiUndoSnapshot === null) {
            return;
        }

        $this->applySnapshot($this->aiUndoSnapshot);
        $this->aiUndoSnapshot = null;
        $this->aiHistory[] = ['role' => 'assistant', 'content' => '• Deshice el último cambio.'];
    }

    /**
     * The editable proposal as the AI sees it.
     *
     * @return array<string, mixed>
     */
    private function formSnapshot(): array
    {
        return [
            'title' => $this->title,
            'project_type' => $this->projectType,
            'what_we_do' => $this->whatWeDo,
            'how_we_do_it' => $this->howWeDoIt,
            'includes' => array_values(array_filter(array_map('trim', explode("\n", $this->includesRaw)))),
            'excludes' => array_values(array_filter(array_map('trim', explode("\n", $this->excludesRaw)))),
            'notes' => $this->notes,
            'valid_until' => $this->validUntil,
            'currency' => $this->currency,
            'discount' => $this->discount,
            'apply_tax' => $this->applyTax,
            'tax_rate' => $this->taxRate,
            'stages' => array_map(fn (array $stage): array => [
                'name' => $stage['name'],
                'description' => $stage['description'] ?? '',
                'duration' => $stage['duration'] ?? '',
            ], $this->stages),
            'items' => array_map(fn (array $item): array => [
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
            ], $this->items),
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    private function applySnapshot(array $snapshot): void
    {
        $this->title = $snapshot['title'];
        $this->projectType = $snapshot['project_type'];
        $this->whatWeDo = $snapshot['what_we_do'];
        $this->howWeDoIt = $snapshot['how_we_do_it'];
        $this->includesRaw = implode("\n", $snapshot['includes']);
        $this->excludesRaw = implode("\n", $snapshot['excludes']);
        $this->notes = $snapshot['notes'];
        $this->validUntil = $snapshot['valid_until'];
        $this->discount = (float) $snapshot['discount'];
        $this->applyTax = (bool) $snapshot['apply_tax'];
        $this->taxRate = (float) $snapshot['tax_rate'];

        $this->stages = collect($snapshot['stages'])->values()->map(fn (array $stage, int $order): array => [
            ...$stage,
            'order' => $order,
        ])->all();

        $this->items = collect($snapshot['items'])->values()->map(fn (array $item, int $order): array => [
            'description' => $item['description'],
            'quantity' => (float) $item['quantity'],
            'unit_price' => (float) $item['unit_price'],
            'subtotal' => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
            'order' => $order,
        ])->all();
    }

    public function save(): void
    {
        $this->validate([
            ...$this->recipientRules(),
            'title' => 'required|string|max:255',
            'projectType' => 'required|string',
            'whatWeDo' => 'required|string',
            'howWeDoIt' => 'required|string',
            'validUntil' => 'nullable|date',
            'discount' => 'numeric|min:0',
            'taxRate' => 'numeric|min:0|max:100',
            'stages.*.name' => 'required|string|max:255',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $includes = array_values(array_filter(array_map('trim', explode("\n", $this->includesRaw))));
        $excludes = array_values(array_filter(array_map('trim', explode("\n", $this->excludesRaw))));

        $this->proposal->update([
            ...$this->recipientAttributes(),
            'title' => $this->title,
            'project_type' => $this->projectType,
            'what_we_do' => $this->whatWeDo,
            'how_we_do_it' => $this->howWeDoIt,
            'includes' => $includes ?: null,
            'excludes' => $excludes ?: null,
            'notes' => $this->notes,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax_rate' => $this->applyTax ? $this->taxRate : 0,
            'total' => $this->total,
            'valid_until' => $this->validUntil ?: null,
        ]);

        // Sync stages
        $this->proposal->stages()->delete();
        foreach ($this->stages as $order => $stage) {
            ProposalStage::create([
                'proposal_id' => $this->proposal->id,
                'name' => $stage['name'],
                'description' => $stage['description'],
                'duration' => $stage['duration'],
                'order' => $order,
            ]);
        }

        // Sync items
        $this->proposal->items()->delete();
        foreach ($this->items as $order => $item) {
            ProposalItem::create([
                'proposal_id' => $this->proposal->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'order' => $order,
            ]);
        }

        $this->redirectRoute('proposals.show', $this->proposal, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.proposal.edit')
            ->layout('components.layouts.app.main', ['title' => 'Editar Proposal']);
    }
}
