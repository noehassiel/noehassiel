<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class Invoices extends Component
{
    public function render(): View
    {
        return view('livewire.portal.invoices', [
            'invoices' => Invoice::query()
                ->whereBelongsTo(Auth::user()->client)
                ->latest('issue_date')
                ->get(),
        ])->title(__('portal.invoices.title'));
    }
}
