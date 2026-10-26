<?php

namespace Database\Factories;

use App\Models\ProjectRequirement;
use App\Models\RequirementFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequirementFile>
 */
class RequirementFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_requirement_id' => ProjectRequirement::factory(),
            'uploaded_by' => null,
            'path' => 'requirements/'.fake()->uuid().'.pdf',
            'original_name' => 'logo.pdf',
            'mime_type' => 'application/pdf',
            'size' => 2048,
        ];
    }
}
