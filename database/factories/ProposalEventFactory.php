<?php

namespace Database\Factories;

use App\Enums\ProposalEventType;
use App\Models\Proposal;
use App\Models\ProposalEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProposalEvent>
 */
class ProposalEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'type' => ProposalEventType::ChangesRequested,
            'body' => fake()->sentence(),
        ];
    }
}
