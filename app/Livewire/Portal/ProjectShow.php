<?php

namespace App\Livewire\Portal;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Mail\RequirementUploadedMail;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\RequirementFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('components.layouts.portal')]
class ProjectShow extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $linkLabel = '';

    public string $linkUrl = '';

    /** @var array<int, array<int, TemporaryUploadedFile>> Pending uploads, keyed by requirement id */
    public array $uploads = [];

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $this->project = $project;
    }

    /**
     * The client shares a folder (Drive, Dropbox, OneDrive…) where they'll upload the project resources.
     */
    public function addLink(): void
    {
        $this->authorize('addLink', $this->project);

        $validated = $this->validate([
            'linkLabel' => ['required', 'string', 'max:255'],
            'linkUrl' => ['required', 'url:https', 'max:2048'],
        ], attributes: [
            'linkLabel' => __('portal.project.folder_name'),
            'linkUrl' => __('portal.project.folder_url'),
        ]);

        $this->project->links()->create([
            'label' => $validated['linkLabel'],
            'url' => $validated['linkUrl'],
            'added_by' => Auth::id(),
        ]);

        $this->reset('linkLabel', 'linkUrl');
    }

    public function removeLink(int $linkId): void
    {
        $link = $this->project->links()->findOrFail($linkId);

        $this->authorize('removeLink', [$this->project, $link]);

        $link->delete();
    }

    /**
     * The client says they uploaded an item to their shared folder.
     */
    public function markUploaded(int $requirementId): void
    {
        $requirement = $this->project->requirements()->findOrFail($requirementId);

        $this->authorize('markRequirementUploaded', [$this->project, $requirement]);

        $this->notifyUploaded($requirement);
    }

    /**
     * The client uploads the files right here instead of sharing a folder.
     */
    public function uploadFiles(int $requirementId): void
    {
        $requirement = $this->project->requirements()->findOrFail($requirementId);

        $this->authorize('markRequirementUploaded', [$this->project, $requirement]);

        abort_if($requirement->status === RequirementStatus::Received, 403);

        $this->validate([
            "uploads.{$requirementId}" => ['required', 'array', 'max:10'],
            "uploads.{$requirementId}.*" => ['file', 'max:10240'],
        ], attributes: [
            "uploads.{$requirementId}" => __('portal.project.files'),
            "uploads.{$requirementId}.*" => __('portal.project.file'),
        ]);

        foreach ($this->uploads[$requirementId] as $upload) {
            $requirement->files()->create([
                'uploaded_by' => Auth::id(),
                'path' => $upload->store("requirements/{$this->project->id}", RequirementFile::diskName()),
                'original_name' => $upload->getClientOriginalName(),
                'mime_type' => $upload->getMimeType(),
                'size' => $upload->getSize(),
            ]);
        }

        unset($this->uploads[$requirementId]);

        $this->notifyUploaded($requirement);
    }

    public function removeFile(int $fileId): void
    {
        $file = RequirementFile::query()
            ->whereHas('requirement', fn ($query) => $query->where('project_id', $this->project->id))
            ->findOrFail($fileId);

        $this->authorize('removeRequirementFile', [$this->project, $file]);

        $file->delete();
    }

    /**
     * Only a real change notifies the admin; double clicks, re-marks or extra uploads stay silent.
     */
    private function notifyUploaded(ProjectRequirement $requirement): void
    {
        if ($requirement->status !== RequirementStatus::Pending) {
            return;
        }

        $requirement->markUploadedByClient();

        Mail::to(config('app.admin_email'))->send(new RequirementUploadedMail($requirement, Auth::user()));
    }

    public function render(): View
    {
        $steps = array_values(array_filter(
            ProjectStatus::cases(),
            fn (ProjectStatus $status): bool => $status !== ProjectStatus::Paused,
        ));

        return view('livewire.portal.project-show', [
            'requirements' => $this->project->requirements()->with('files')->get(),
            'links' => $this->project->links()->get(),
            'steps' => $steps,
            'currentStep' => array_search($this->project->status, $steps, true),
        ])->title($this->project->name);
    }
}
