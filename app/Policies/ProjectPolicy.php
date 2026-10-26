<?php

namespace App\Policies;

use App\Enums\RequirementStatus;
use App\Models\Project;
use App\Models\ProjectLink;
use App\Models\ProjectRequirement;
use App\Models\RequirementFile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ProjectPolicy
{
    /**
     * The admin sees every project; a client user only their own client's.
     */
    public function view(User $user, Project $project): bool
    {
        return Gate::forUser($user)->allows('access-admin') || $user->canSeeProject($project);
    }

    /**
     * The client team can share their own folders on their project.
     */
    public function addLink(User $user, Project $project): bool
    {
        return $user->canSeeProject($project);
    }

    /**
     * A client user can only remove folders they shared; the admin's stay.
     */
    public function removeLink(User $user, Project $project, ProjectLink $link): bool
    {
        return $this->addLink($user, $project)
            && $link->project_id === $project->id
            && $link->added_by === $user->id;
    }

    /**
     * Only the client team can tell the admin they uploaded something.
     */
    public function markRequirementUploaded(User $user, Project $project, ProjectRequirement $requirement): bool
    {
        return $user->canSeeProject($project)
            && $requirement->project_id === $project->id;
    }

    /**
     * A client user can only remove their own uploads, and only until the admin confirms receipt.
     */
    public function removeRequirementFile(User $user, Project $project, RequirementFile $file): bool
    {
        return $user->canSeeProject($project)
            && $file->requirement->project_id === $project->id
            && $file->uploaded_by === $user->id
            && $file->requirement->status !== RequirementStatus::Received;
    }
}
