<?php

namespace App\Livewire\Proposal\Concerns;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

/**
 * Lets a proposal form address either a client or a lead through a single
 * "client:{id}" / "lead:{id}" select value.
 */
trait SelectsRecipient
{
    public string $recipient = '';

    #[Computed]
    public function clients(): Collection
    {
        return Client::query()->orderBy('name')->get();
    }

    #[Computed]
    public function leads(): Collection
    {
        return Lead::query()->latest()->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function recipientRules(): array
    {
        return [
            'recipient' => ['required', 'regex:/^(client|lead):\d+$/', function (string $attribute, string $value, \Closure $fail): void {
                [$type, $id] = explode(':', $value);
                $model = $type === 'client' ? Client::class : Lead::class;

                if (! $model::whereKey($id)->exists()) {
                    $fail('Selecciona un cliente o lead válido.');
                }
            }],
        ];
    }

    /**
     * @return array{client_id: int|null, lead_id: int|null}
     */
    protected function recipientAttributes(): array
    {
        [$type, $id] = explode(':', $this->recipient);

        return [
            'client_id' => $type === 'client' ? (int) $id : null,
            'lead_id' => $type === 'lead' ? (int) $id : null,
        ];
    }
}
