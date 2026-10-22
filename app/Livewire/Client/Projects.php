<?php

namespace App\Livewire\Client;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Models\Client;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Projects extends Component
{
    public Client $client;

    public string $name = '';

    public string $description = '';

    public string $status = 'brief';

    public function openCreate(): void
    {
        $this->reset('name', 'description', 'status');
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'project-create-modal');
    }

    /**
     * Create the project and jump to its page to fill in the checklist.
     */
    public function create(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
        ]);

        $project = $this->client->projects()->create($validated);

        $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.client.projects', [
            'projects' => $this->client->projects()
                ->withCount([
                    'requirements as pending_count' => fn ($query) => $query->where('status', RequirementStatus::Pending),
                    'requirements as to_review_count' => fn ($query) => $query->where('status', RequirementStatus::ClientMarked),
                ])
                ->latest()
                ->get(),
            'statuses' => ProjectStatus::cases(),
        ]);
    }
}
