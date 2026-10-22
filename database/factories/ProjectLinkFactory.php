<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectLink>
 */
class ProjectLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'label' => 'Recursos de marca',
            'url' => 'https://drive.google.com/drive/folders/'.fake()->regexify('[A-Za-z0-9_-]{33}'),
        ];
    }
}
