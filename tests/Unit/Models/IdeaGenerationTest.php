<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;

it('has expected fillable attributes', function () {
    $generation = new IdeaGeneration;

    expect($generation->getFillable())->toBe([
        'occupation_name',
        'occupation_description',
    ]);
});

it('has many generated ideas', function () {
    $generation = IdeaGeneration::factory()->create();

    GeneratedIdea::factory()->count(2)->for($generation, 'generation')->create();

    expect($generation->ideas)->toHaveCount(2);
    expect($generation->ideas->first())->toBeInstanceOf(GeneratedIdea::class);
});

it('cascades delete to generated ideas', function () {
    $generation = createGenerationWithIdeas(2);

    $generation->delete();

    expect(GeneratedIdea::count())->toBe(0);
});
