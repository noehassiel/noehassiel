<?php

namespace App\Livewire\Proposal;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public ?int $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function statuses(): array
    {
        return ProposalStatus::cases();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
    }

    public function delete(): void
    {
        $proposal = Proposal::findOrFail($this->deletingId);
        $proposal->delete();

        $this->deletingId = null;
        $this->dispatch('toast', message: 'Proposal eliminado.', type: 'success');
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function render(): \Illuminate\View\View
    {
        $proposals = Proposal::query()
            ->with('client')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('number', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.proposal.index', compact('proposals'))
            ->layout('components.layouts.app.main', ['title' => 'Proposals']);
    }
}
