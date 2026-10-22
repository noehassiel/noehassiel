<?php

namespace App\Livewire\Proposal;

use App\Ai\Agents\ProposalDrafter;
use App\Enums\ProposalProjectType;
use App\Livewire\Proposal\Concerns\SelectsRecipient;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\ProposalStage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class Create extends Component
{
    use SelectsRecipient;

    public string $title = '';

    public string $projectType = '';

    public string $whatWeDo = '';

    public string $howWeDoIt = '';

    public string $notes = '';

    public string $currency = 'MXN';

    public string $validUntil = '';

    // Includes / Excludes (stored as newline-separated for easy textarea editing)
    public string $includesRaw = '';

    public string $excludesRaw = '';

    // Stages: array of ['name', 'description', 'duration', 'order']
    public array $stages = [];

    // Items: array of ['description', 'quantity', 'unit_price', 'subtotal', 'order']
    public array $items = [];

    // Financials
    public float $discount = 0;

    public float $taxRate = 16;

    public bool $applyTax = true;

    // AI draft
    public string $draftNotes = '';

    /** @var array<int, string> */
    public array $draftServices = [];

    /** @var array<int, string> */
    public array $openQuestions = [];

    public function mount(): void
    {
        $this->addStage();
        $this->addItem();
    }

    #[Computed]
    public function projectTypes(): array
    {
        return ProposalProjectType::cases();
    }

    #[Computed]
    public function services(): array
    {
        return ProposalProjectType::services();
    }

    /**
     * Fill the form with an AI first draft built from the notes. Prices stay at 0 on purpose.
     */
    public function draftWithAi(): void
    {
        $this->validate([
            ...$this->recipientRules(),
            'draftNotes' => 'required|string|min:20|max:20000',
            'draftServices' => 'array',
            'draftServices.*' => [Rule::enum(ProposalProjectType::class)],
        ]);

        try {
            $draft = (new ProposalDrafter)->prompt($this->draftPrompt());
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('draftNotes', 'No se pudo generar el borrador. Intenta de nuevo.');

            return;
        }

        $this->title = $draft['title'];
        $this->projectType = $draft['project_type'];
        $this->whatWeDo = $draft['what_we_do'];
        $this->howWeDoIt = $draft['how_we_do_it'];
        $this->includesRaw = implode("\n", $draft['includes']);
        $this->excludesRaw = implode("\n", $draft['excludes']);
        $this->openQuestions = $draft['open_questions'];

        $this->stages = collect($draft['stages'])->values()->map(fn (array $stage, int $order): array => [
            'name' => $stage['name'],
            'description' => $stage['description'],
            'duration' => $stage['duration'],
            'order' => $order,
        ])->all();

        $this->items = collect($draft['items'])->values()->map(fn (array $item, int $order): array => [
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => 0,
            'subtotal' => 0,
            'order' => $order,
        ])->all();

        $this->dispatch('toast', message: 'Borrador listo. Revisa el texto y pon los precios.', type: 'success');
    }

    /** The notes plus everything we already know about who the proposal is for. */
    private function draftPrompt(): string
    {
        [$type, $id] = explode(':', $this->recipient);
        $recipient = $type === 'client' ? Client::find($id) : Lead::find($id);

        $context = collect([
            'Recipient' => $recipient->name.($type === 'lead' ? ' (prospect)' : ' (existing client)'),
            'Company' => $recipient->company ?? null,
            'What they wrote when they contacted us' => $type === 'lead' ? $recipient->message : null,
            'Budget range they selected' => $type === 'lead' ? $recipient->budget : null,
            'Timeline they selected' => $type === 'lead' ? $recipient->timeline : null,
            'Services to include' => collect($this->draftServices)
                ->map(fn (string $service): string => ProposalProjectType::from($service)->label())
                ->implode(', ') ?: null,
        ])->filter()->map(fn (string $value, string $label): string => "{$label}: {$value}")->implode("\n");

        return "{$context}\n\nNotes:\n{$this->draftNotes}";
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
        // Recalculate subtotal for the changed item
        [$index, $field] = explode('.', $key, 2);

        if (in_array($field, ['quantity', 'unit_price'])) {
            $qty = (float) ($this->items[(int) $index]['quantity'] ?? 1);
            $price = (float) ($this->items[(int) $index]['unit_price'] ?? 0);
            $this->items[(int) $index]['subtotal'] = round($qty * $price, 2);
        }
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

        $proposal = Proposal::create([
            ...$this->recipientAttributes(),
            'number' => Proposal::generateNumber(),
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

        foreach ($this->stages as $order => $stage) {
            ProposalStage::create([
                'proposal_id' => $proposal->id,
                'name' => $stage['name'],
                'description' => $stage['description'],
                'duration' => $stage['duration'],
                'order' => $order,
            ]);
        }

        foreach ($this->items as $order => $item) {
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
                'order' => $order,
            ]);
        }

        $this->redirectRoute('proposals.show', $proposal, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.proposal.create')
            ->layout('components.layouts.app.main', ['title' => 'Nuevo Proposal']);
    }
}
