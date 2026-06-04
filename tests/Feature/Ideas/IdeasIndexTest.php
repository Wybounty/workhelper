<?php

use App\Models\IdeaGeneration;

it('redirects to generations index when history is empty', function () {
    $response = $this->get(route('ideas.index'));

    $response->assertRedirect(route('generations.index'));
});

it('redirects to the latest generation when one exists', function () {
    IdeaGeneration::factory()->create(['created_at' => now()->subDay()]);
    $latest = IdeaGeneration::factory()->create(['created_at' => now()]);

    $this->get(route('ideas.index'))
        ->assertRedirect(route('generations.show', $latest));
});
