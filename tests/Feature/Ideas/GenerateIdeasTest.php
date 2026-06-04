<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;
use App\Models\Occupation;
use Illuminate\Support\Facades\Http;

it('fails when no occupation exists', function () {
    $response = $this->get(route('ideas.generate'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Aucun métier trouvé. Importez d\'abord les occupations.');

    Http::assertNothingSent();
    expect(IdeaGeneration::count())->toBe(0);
});

it('calls the n8n webhook with occupation data', function () {
    Occupation::factory()->create([
        'id' => 1,
        'name' => 'Data analyst',
        'description' => 'Analyse des données métier.',
    ]);

    fakeN8nWebhookSuccess();

    $this->get(route('ideas.generate'));

    Http::assertSent(function ($request) {
        return $request->url() === config('services.n8n.webhook')
            && $request['brief']['job'] === 'Data analyst'
            && $request['brief']['description'] === 'Analyse des données métier.';
    });
});

it('saves the generation and three ideas on success', function () {
    Occupation::factory()->create(['id' => 1]);

    fakeN8nWebhookSuccess();

    $this->get(route('ideas.generate'));

    expect(IdeaGeneration::count())->toBe(1);
    expect(GeneratedIdea::count())->toBe(3);

    $generation = IdeaGeneration::first();

    expect($generation->occupation_name)->not->toBeEmpty();
    expect($generation->ideas)->toHaveCount(3);
    expect($generation->ideas->first()->title)->toBe('Projet test 1');
});

it('redirects to the generation detail page with a success flash', function () {
    Occupation::factory()->create(['id' => 1]);

    fakeN8nWebhookSuccess();

    $response = $this->get(route('ideas.generate'));

    $generation = IdeaGeneration::first();

    $response
        ->assertRedirect(route('generations.show', $generation))
        ->assertSessionHas('success', 'Idées générées et enregistrées avec succès.');
});

it('redirects home with error when the webhook fails', function () {
    Occupation::factory()->create(['id' => 1]);

    fakeN8nWebhookFailure();

    $response = $this->get(route('ideas.generate'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Impossible de générer les idées pour le moment.');

    expect(IdeaGeneration::count())->toBe(0);
});

it('redirects home with error when the webhook returns no projects', function () {
    Occupation::factory()->create(['id' => 1]);

    Http::fake([
        config('services.n8n.webhook') => Http::response(['projects' => []], 200),
    ]);

    $response = $this->get(route('ideas.generate'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Réponse invalide : aucun projet reçu.');
});

it('accepts projects wrapped in a projects key', function () {
    Occupation::factory()->create(['id' => 1]);

    Http::fake([
        config('services.n8n.webhook') => Http::response([
            'projects' => sampleProjectsPayload(),
        ], 200),
    ]);

    $this->get(route('ideas.generate'));

    expect(GeneratedIdea::count())->toBe(3);
});
