<?php

namespace Database\Factories;

use App\Models\IdeaGeneration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdeaGeneration>
 */
class IdeaGenerationFactory extends Factory
{
    protected $model = IdeaGeneration::class;

    public function definition(): array
    {
        return [
            'occupation_name' => fake()->jobTitle(),
            'occupation_description' => fake()->paragraph(),
        ];
    }
}
