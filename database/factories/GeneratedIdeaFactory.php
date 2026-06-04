<?php

namespace Database\Factories;

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneratedIdea>
 */
class GeneratedIdeaFactory extends Factory
{
    protected $model = GeneratedIdea::class;

    public function definition(): array
    {
        return [
            'generation_id' => IdeaGeneration::factory(),
            'title' => fake()->sentence(4),
            'application_type' => fake()->randomElement(['Web', 'Mobile', 'Automatisation']),
            'description' => fake()->paragraph(),
            'why_useful' => fake()->paragraph(),
            'development_duration' => fake()->randomElement(['2 mois', '3 mois', '6 mois']),
            'recommended_stack' => 'Laravel, Vue 3, Inertia, Tailwind CSS',
            'business_model' => 'Abonnement SaaS',
            'estimated_monthly_revenue' => (string) fake()->numberBetween(500, 5000),
        ];
    }
}
