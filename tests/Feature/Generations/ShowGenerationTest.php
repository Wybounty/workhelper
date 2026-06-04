<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;
use Inertia\Testing\AssertableInertia as Assert;

it('renders a generation detail page with its ideas', function () {
    $generation = createGenerationWithIdeas(3);

    $response = $this->get(route('generations.show', $generation));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Generations/Show')
            ->has('generation', fn (Assert $gen) => $gen
                ->where('id', $generation->id)
                ->where('occupation_name', $generation->occupation_name)
                ->where('occupation_description', $generation->occupation_description)
                ->has('created_at')
                ->has('projects', 3)
                ->has('projects.0', fn (Assert $project) => $project
                    ->has('title')
                    ->has('application_type')
                    ->has('description')
                    ->has('why_useful')
                    ->has('development_duration')
                    ->has('recommended_stack')
                    ->has('business_model')
                    ->has('estimated_monthly_revenue')
                )
            )
        );
});

it('loads ideas from the database for the generation', function () {
    $generation = IdeaGeneration::factory()->create([
        'occupation_name' => 'Infirmier',
    ]);

    GeneratedIdea::factory()->for($generation, 'generation')->create([
        'title' => 'Téléconsultation santé',
    ]);

    $this->get(route('generations.show', $generation))
        ->assertInertia(fn (Assert $page) => $page
            ->where('generation.projects.0.title', 'Téléconsultation santé')
        );
});

it('returns 404 for a missing generation', function () {
    $this->get(route('generations.show', ['generation' => 99999]))
        ->assertNotFound();
});
