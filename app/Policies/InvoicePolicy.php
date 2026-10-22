<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class InvoicePolicy
{
    /**
     * The admin downloads any invoice; a client user only their own client's.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        return Gate::forUser($user)->allows('access-admin')
            || ($user->isClient() && $user->client_id === $invoice->client_id);
    }
}
