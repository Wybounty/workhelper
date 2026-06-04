<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;

it('has expected fillable attributes', function () {
    $idea = new GeneratedIdea;

    expect($idea->getFillable())->toContain(
        'generation_id',
        'title',
        'application_type',
        'description',
        'why_useful',
        'development_duration',
        'recommended_stack',
        'business_model',
        'estimated_monthly_revenue',
    );
});

it('belongs to a generation', function () {
    $generation = IdeaGeneration::factory()->create();
    $idea = GeneratedIdea::factory()->for($generation, 'generation')->create();

    expect($idea->generation)->toBeInstanceOf(IdeaGeneration::class);
    expect($idea->generation->is($generation))->toBeTrue();
});
