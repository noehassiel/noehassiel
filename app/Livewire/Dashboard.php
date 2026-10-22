<?php

namespace App\Livewire;

use App\Enums\ProposalStatus;
use App\Enums\RequirementStatus;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\Proposal;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $proposalsByStatus = Proposal::query()
            ->selectRaw('status, count(*) as count, coalesce(sum(total), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Proposal $row) => $row->status->value);

        $acceptedThisYear = Proposal::query()
            ->where('status', ProposalStatus::Accepted)
            ->whereYear('updated_at', now()->year)
            ->sum('total');

        $activeProjects = Project::query()
            ->active()
            ->with('client')
            ->withCount('outstandingRequirements')
            ->orderByRaw('due_on is null, due_on')
            ->limit(6)
            ->get();

        $waitingOnClients = ProjectRequirement::query()
            ->where('status', '!=', RequirementStatus::Received)
            ->with('project.client')
            ->orderByRaw('due_on is null, due_on')
            ->limit(6)
            ->get();

        $unpaidInvoices = Invoice::query()->where('payment_status', '!=', 'paid');

        return view('livewire.dashboard', [
            'statuses' => ProposalStatus::cases(),
            'proposalsByStatus' => $proposalsByStatus,
            'proposalsTotalCount' => max(1, $proposalsByStatus->sum('count')),
            'pipelineAmount' => (float) ($proposalsByStatus->get(ProposalStatus::Sent->value)?->amount ?? 0),
            'acceptedThisYear' => (float) $acceptedThisYear,
            'activeProjectsCount' => Project::query()->active()->count(),
            'activeProjects' => $activeProjects,
            'waitingOnClients' => $waitingOnClients,
            'unpaidCount' => (clone $unpaidInvoices)->count(),
            'unpaidAmount' => (float) $unpaidInvoices->sum('total'),
            'recentLeads' => Lead::query()->latest()->limit(5)->get(),
        ])->layout('components.layouts.app.main', ['title' => __('Dashboard')]);
    }
}
