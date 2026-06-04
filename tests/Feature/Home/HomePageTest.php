<?php

use App\Models\Occupation;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page with inertia', function () {
    Occupation::factory()->count(2)->create();

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('occupations_count', 2)
            ->has('esco_file', fn (Assert $file) => $file
                ->has('present')
                ->where('name', 'occupations.json')
                ->where('relative_path', 'database/data/occupations.json')
            )
        );
});

it('reports esco file as missing when occupations.json is absent', function () {
    removeOccupationsJsonFile();

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('esco_file.present', false)
            ->where('occupations_count', 0)
        );
});

it('reports esco file as present when occupations.json exists', function () {
    writeOccupationsJsonFile([
        ['preferredLabel' => 'Test', 'description' => null],
    ]);

    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('esco_file.present', true)
        );
});
