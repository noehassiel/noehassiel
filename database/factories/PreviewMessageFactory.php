<?php

namespace Database\Factories;

use App\Models\PreviewMessage;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PreviewMessage>
 */
class PreviewMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'role' => 'user',
            'content' => fake()->sentence(),
            'page_url' => 'https://acme.preview.noehassiel.com/',
        ];
    }

    public function fromAssistant(): static
    {
        return $this->state(['role' => 'assistant', 'page_url' => null]);
    }
}
