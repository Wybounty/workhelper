<?php

use App\Models\Occupation;

beforeEach(function () {
    removeOccupationsJsonFile();
});

afterEach(function () {
    removeOccupationsJsonFile();
});

it('imports occupations from a valid json file', function () {
    writeOccupationsJsonFile([
        ['preferredLabel' => 'Développeur web', 'description' => 'Crée des applications web.'],
        ['preferredLabel' => 'Designer UX', 'description' => 'Conçoit des interfaces.'],
    ]);

    $response = $this->get(route('occupations.import'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('success', 'Intégration ESCO terminée. 2 métiers disponibles dans la base.');

    expect(Occupation::count())->toBe(2);
    expect(Occupation::where('name', 'Développeur web')->exists())->toBeTrue();
});

it('fails gracefully when the occupations file is missing', function () {
    $response = $this->get(route('occupations.import'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Fichier occupations.json introuvable.');

    expect(Occupation::count())->toBe(0);
});

it('fails gracefully when the json file is invalid', function () {
    $directory = database_path('data');

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    file_put_contents(occupationsJsonPath(), 'not-valid-json');

    $response = $this->get(route('occupations.import'));

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHas('error', 'Le fichier occupations.json est invalide.');

    expect(Occupation::count())->toBe(0);
});

it('does not create duplicates when importing the same occupations twice', function () {
    writeOccupationsJsonFile([
        ['preferredLabel' => 'Conducteur de tramway', 'description' => 'Opère un tramway.'],
    ]);

    $this->get(route('occupations.import'));
    $this->get(route('occupations.import'));

    expect(Occupation::count())->toBe(1);
});

it('updates descriptions on upsert when re-importing', function () {
    writeOccupationsJsonFile([
        ['preferredLabel' => 'Chef de projet', 'description' => 'Ancienne description.'],
    ]);

    $this->get(route('occupations.import'));

    writeOccupationsJsonFile([
        ['preferredLabel' => 'Chef de projet', 'description' => 'Description mise à jour.'],
    ]);

    $this->get(route('occupations.import'));

    expect(Occupation::count())->toBe(1);
    expect(Occupation::first()->description)->toBe('Description mise à jour.');
});
