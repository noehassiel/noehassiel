<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Crud extends Component
{
    public $client;

    public $name = '';
    public $phone = '';
    public $address = '';
    public $logo = '';
    public $email = '';
    public $website = '';

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        if ($this->client) {
            $client = Client::find($id);
            $client->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'logo' => $this->logo,
                'email' => $this->email,
                'website' => $this->website,
            ]);

            session()->flash('message', 'Client updated successfully.');
        } else {

            Client::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'logo' => $this->logo,
                'email' => $this->email,
                'website' => $this->website,
            ]);

            // Create folder for client
            $clientFolder = 'clients/' . Str::slug($this->name);
            if (!Storage::exists($clientFolder)) {
                Storage::makeDirectory($clientFolder);
            }
            // Create folder for client invoices
            $invoicesFolder = $clientFolder . '/invoices';
            if (!Storage::exists($invoicesFolder)) {
                Storage::makeDirectory($invoicesFolder);
            }

            session()->flash('message', 'Client created successfully.');
        }
    }


    public function render()
    {
        return view('livewire.client.crud');
    }
}
