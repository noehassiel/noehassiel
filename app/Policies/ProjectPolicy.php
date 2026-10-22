<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ProjectPolicy
{
    /**
     * The admin sees every project; a client user only their own client's.
     */
    public function view(User $user, Project $project): bool
    {
        return Gate::forUser($user)->allows('access-admin')
            || ($user->isClient() && $user->client_id === $project->client_id);
    }

    /**
     * Only the client team can tell the admin they uploaded something.
     */
    public function markRequirementUploaded(User $user, Project $project, ProjectRequirement $requirement): bool
    {
        return $user->isClient()
            && $user->client_id === $project->client_id
            && $requirement->project_id === $project->id;
    }
}
