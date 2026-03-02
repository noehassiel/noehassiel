<?php

namespace App\Livewire\Client\Invoices;

use Livewire\Component;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Resend\Laravel\Facades\Resend;

use App\Models\Client;
use App\Models\Invoice;

class Crud extends Component
{


    public function sendInvoice()
    {
        Resend::emails()->send([
            'from' => 'noehassiel <no-reply@noehassiel.com>',
            'to' => 'hello@noehassiel.com',
            'subject' => 'hello world',
            'text' => 'hello world',
        ]);
    }

    public function render()
    {
        return view('livewire.client.invoices.crud');
    }
}
