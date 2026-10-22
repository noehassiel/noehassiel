<?php

namespace App\Livewire\Lead;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->validate(['status' => ['nullable', Rule::enum(LeadStatus::class)]]);
        $this->resetPage();
    }

    public function render(): View
    {
        $leads = Lead::query()
            ->when($this->search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('company', 'like', "%{$this->search}%")
            ))
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(20);

        return view('livewire.lead.index', [
            'leads' => $leads,
            'statuses' => LeadStatus::cases(),
            'openCount' => Lead::query()->whereIn('status', [LeadStatus::New, LeadStatus::Contacted, LeadStatus::ProposalSent])->count(),
        ])->layout('components.layouts.app.main', ['title' => 'Leads']);
    }
}
