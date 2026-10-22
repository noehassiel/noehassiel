<?php

namespace App\Livewire\Project;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Project $project;

    public string $name = '';

    public string $description = '';

    public string $status = '';

    public string $progressNote = '';

    public ?string $startsOn = null;

    public ?string $dueOn = null;

    public string $linkLabel = '';

    public string $linkUrl = '';

    public string $requirementTitle = '';

    public string $requirementDescription = '';

    public ?string $requirementDueOn = null;

    public function mount(Project $project): void
    {
        $this->project = $project;

        $this->fill([
            'name' => $project->name,
            'description' => (string) $project->description,
            'status' => $project->status->value,
            'progressNote' => (string) $project->progress_note,
            'startsOn' => $project->starts_on?->toDateString(),
            'dueOn' => $project->due_on?->toDateString(),
        ]);
    }

    public function saveDetails(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'progressNote' => ['nullable', 'string', 'max:2000'],
            'startsOn' => ['nullable', 'date'],
            'dueOn' => ['nullable', 'date', 'after_or_equal:startsOn'],
        ]);

        $this->project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'status' => $validated['status'],
            'progress_note' => $validated['progressNote'] ?: null,
            'starts_on' => $validated['startsOn'] ?: null,
            'due_on' => $validated['dueOn'] ?: null,
        ]);

        $this->dispatch('project-saved');
    }

    public function addLink(): void
    {
        $validated = $this->validate([
            'linkLabel' => ['required', 'string', 'max:255'],
            'linkUrl' => ['required', 'url:https', 'max:2048'],
        ]);

        $this->project->links()->create([
            'label' => $validated['linkLabel'],
            'url' => $validated['linkUrl'],
        ]);

        $this->reset('linkLabel', 'linkUrl');
    }

    public function removeLink(int $linkId): void
    {
        $this->project->links()->findOrFail($linkId)->delete();
    }

    public function addRequirement(): void
    {
        $validated = $this->validate([
            'requirementTitle' => ['required', 'string', 'max:255'],
            'requirementDescription' => ['nullable', 'string', 'max:2000'],
            'requirementDueOn' => ['nullable', 'date'],
        ]);

        $this->project->requirements()->create([
            'title' => $validated['requirementTitle'],
            'description' => $validated['requirementDescription'] ?: null,
            'due_on' => $validated['requirementDueOn'] ?: null,
            'position' => (int) $this->project->requirements()->max('position') + 1,
        ]);

        $this->reset('requirementTitle', 'requirementDescription', 'requirementDueOn');
    }

    public function markReceived(int $requirementId): void
    {
        $this->project->requirements()->findOrFail($requirementId)->markReceived();
    }

    public function markPending(int $requirementId): void
    {
        $this->project->requirements()->findOrFail($requirementId)->markPending();
    }

    public function removeRequirement(int $requirementId): void
    {
        $this->project->requirements()->findOrFail($requirementId)->delete();
    }

    public function deleteProject(): void
    {
        $client = $this->project->client;

        $this->project->delete();

        $this->redirectRoute('clients.show', $client, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.project.show', [
            'links' => $this->project->links()->get(),
            'requirements' => $this->project->requirements()->get(),
            'statuses' => ProjectStatus::cases(),
        ])->layout('components.layouts.app.main', ['title' => $this->project->name]);
    }
}
