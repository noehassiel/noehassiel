<?php

namespace App\Livewire\Portal;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Mail\RequirementUploadedMail;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class ProjectShow extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $this->project = $project;
    }

    /**
     * The client says they uploaded an item to their shared folder.
     */
    public function markUploaded(int $requirementId): void
    {
        $requirement = $this->project->requirements()->findOrFail($requirementId);

        $this->authorize('markRequirementUploaded', [$this->project, $requirement]);

        // Only a real change notifies the admin; double clicks or re-marks stay silent
        if ($requirement->status !== RequirementStatus::Pending) {
            return;
        }

        $requirement->markUploadedByClient();

        Mail::to(config('app.admin_email'))->queue(new RequirementUploadedMail($requirement, Auth::user()));
    }

    public function render(): View
    {
        $steps = array_values(array_filter(
            ProjectStatus::cases(),
            fn (ProjectStatus $status): bool => $status !== ProjectStatus::Paused,
        ));

        return view('livewire.portal.project-show', [
            'requirements' => $this->project->requirements()->get(),
            'links' => $this->project->links()->get(),
            'steps' => $steps,
            'currentStep' => array_search($this->project->status, $steps, true),
        ])->title($this->project->name);
    }
}
