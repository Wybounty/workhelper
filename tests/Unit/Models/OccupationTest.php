<?php

use App\Models\Occupation;

it('has expected fillable attributes', function () {
    $occupation = new Occupation;

    expect($occupation->getFillable())->toBe(['name', 'description']);
});

it('can be persisted via factory', function () {
    $occupation = Occupation::factory()->create([
        'name' => 'Plombier',
        'description' => 'Installe des réseaux.',
    ]);

    expect($occupation->exists)->toBeTrue();
    expect($occupation->name)->toBe('Plombier');
});
