<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatus;
use App\Enums\RequirementStatus;
use App\Mail\PendingRequirementsMail;
use App\Models\Client;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

#[Signature('portal:send-reminders')]
#[Description('Email each client team the deliverables they still owe, at most every few days')]
class SendPortalReminders extends Command
{
    public function handle(): int
    {
        $cutoff = now()->subDays(config('app.portal_reminder_days'));

        $clients = Client::query()
            ->where(fn (Builder $query) => $query->whereNull('reminded_at')->orWhere('reminded_at', '<=', $cutoff))
            ->whereHas('users')
            ->whereHas('projects', fn (Builder $query) => $this->remindableProjects($query))
            ->with([
                'users',
                'projects' => fn ($query) => $this->remindableProjects($query)->with([
                    'requirements' => fn ($query) => $query->where('status', RequirementStatus::Pending),
                ]),
            ])
            ->get();

        foreach ($clients as $client) {
            foreach ($client->users as $user) {
                // Members only hear about the projects they were given
                $projects = $client->projects->filter(fn ($project) => $user->canSeeProject($project));

                if ($projects->isNotEmpty()) {
                    Mail::to($user)->send(new PendingRequirementsMail($user, $projects->values()));
                }
            }

            $client->forceFill(['reminded_at' => now()])->save();
        }

        $this->info("Reminders sent to {$clients->count()} client(s).");

        return self::SUCCESS;
    }

    /**
     * Ongoing projects (not paused or delivered) that still have pending items.
     * Takes the Builder contract because eager loading passes a relation, not a query builder.
     */
    private function remindableProjects(Builder $query): Builder
    {
        return $query
            ->whereNotIn('status', [ProjectStatus::Delivered, ProjectStatus::Paused])
            ->whereHas('requirements', fn (Builder $query) => $query->where('status', RequirementStatus::Pending));
    }
}
