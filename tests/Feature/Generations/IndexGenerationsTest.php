<?php

use App\Models\IdeaGeneration;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the generations history page with inertia', function () {
    $generation = IdeaGeneration::factory()->create();
    $generation->ideas()->create([
        'title' => 'Idée A',
        'application_type' => 'Web',
        'description' => 'Desc',
        'why_useful' => 'Utile',
        'development_duration' => '2 mois',
        'recommended_stack' => 'Laravel',
        'business_model' => 'SaaS',
        'estimated_monthly_revenue' => '1000',
    ]);

    $response = $this->get(route('generations.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Generations/Index')
            ->has('generations.data', 1)
            ->where('generations.data.0.id', $generation->id)
            ->where('generations.data.0.occupation_name', $generation->occupation_name)
            ->where('generations.data.0.ideas_count', 1)
            ->has('generations.data.0.created_at')
        );
});

it('paginates generations twelve per page', function () {
    IdeaGeneration::factory()->count(15)->create();

    $response = $this->get(route('generations.index'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Generations/Index')
            ->where('generations.per_page', 12)
            ->where('generations.total', 15)
            ->has('generations.data', 12)
            ->has('generations.links')
        );
});

it('returns the second page of generations', function () {
    IdeaGeneration::factory()->count(15)->create();

    $response = $this->get(route('generations.index', ['page' => 2]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('generations.current_page', 2)
            ->has('generations.data', 3)
        );
});
