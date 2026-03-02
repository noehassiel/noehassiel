<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Livewire\Component;

class Show extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client->load(['proposals', 'invoices']);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.client.show')
            ->layout('components.layouts.app.main', ['title' => $this->client->name]);
    }
}
