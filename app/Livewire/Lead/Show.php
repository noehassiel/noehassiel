<?php

namespace App\Livewire\Lead;

use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Lead $lead;

    public string $status = '';

    public string $notes = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
        $this->status = $lead->status->value;
        $this->notes = (string) $lead->notes;
    }

    /**
     * Move the lead along the pipeline. "Won" is only reachable by converting it.
     */
    public function updatedStatus(): void
    {
        if ($this->lead->isConverted()) {
            $this->status = $this->lead->status->value;

            return;
        }

        $this->validate(['status' => ['required', Rule::in(array_map(fn (LeadStatus $status) => $status->value, LeadStatus::selectable()))]]);

        $this->lead->update(['status' => $this->status]);
    }

    public function saveNotes(): void
    {
        $this->validate(['notes' => ['nullable', 'string', 'max:5000']]);

        $this->lead->update(['notes' => $this->notes ?: null]);

        $this->dispatch('lead-notes-saved');
    }

    /**
     * Close the deal and continue on the client page, where the team can be invited to the portal.
     */
    public function convert(): void
    {
        if ($this->lead->isConverted()) {
            $this->redirectRoute('clients.show', $this->lead->client, navigate: true);

            return;
        }

        $client = $this->lead->convertToClient();

        $this->redirectRoute('clients.show', $client, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lead.show', [
            'proposals' => $this->lead->proposals()->latest()->get(),
            'statuses' => LeadStatus::selectable(),
        ])->layout('components.layouts.app.main', ['title' => $this->lead->name]);
    }
}
