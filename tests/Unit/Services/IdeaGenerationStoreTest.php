<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;
use App\Services\IdeaGenerationStore;

it('stores a generation with up to three ideas in a transaction', function () {
    $store = new IdeaGenerationStore;

    $generation = $store->store(
        'Architecte logiciel',
        'Conçoit des systèmes logiciels.',
        [
            ...sampleProjectsPayload(),
            sampleProjectPayload(99),
        ],
    );

    expect($generation)->toBeInstanceOf(IdeaGeneration::class);
    expect($generation->occupation_name)->toBe('Architecte logiciel');
    expect($generation->ideas)->toHaveCount(3);
    expect(GeneratedIdea::count())->toBe(3);
});

it('normalizes missing project fields with defaults', function () {
    $store = new IdeaGenerationStore;

    $generation = $store->store('Métier test', null, [
        ['title' => 'Minimal'],
    ]);

    $idea = $generation->ideas->first();

    expect($idea->title)->toBe('Minimal');
    expect($idea->application_type)->toBe('—');
    expect($idea->estimated_monthly_revenue)->toBe('—');
});
