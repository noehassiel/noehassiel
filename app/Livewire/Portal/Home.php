<?php

namespace App\Livewire\Portal;

use App\Enums\RequirementStatus;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class Home extends Component
{
    /**
     * Active projects of the signed-in user's client, with what is still owed.
     *
     * @return Collection<int, Project>
     */
    #[Computed]
    public function projects(): Collection
    {
        return Project::query()
            ->visibleTo(Auth::user())
            ->active()
            ->withCount(['requirements as outstanding_count' => fn ($query) => $query->where('status', RequirementStatus::Pending)])
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Invoice>
     */
    #[Computed]
    public function recentInvoices(): Collection
    {
        if (! Auth::user()->canSeeInvoices()) {
            return new Collection;
        }

        return Invoice::query()
            ->whereBelongsTo(Auth::user()->client)
            ->latest('issue_date')
            ->limit(3)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.portal.home')->title(__('portal.home.title'));
    }
}
