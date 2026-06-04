<?php

use Barryvdh\DomPDF\Facade\Pdf;

it('generates and downloads a pdf with a valid payload', function () {
    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.idea', \Mockery::type('array'))
        ->andReturnSelf();

    Pdf::shouldReceive('setPaper')
        ->once()
        ->with('a4', 'portrait')
        ->andReturnSelf();

    Pdf::shouldReceive('download')
        ->once()
        ->with('projet-test-1.pdf')
        ->andReturn(response('pdf-binary', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="projet-test-1.pdf"',
        ]));

    $response = $this->post(route('ideas.pdf'), validPdfRequestPayload());

    $response
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('slugifies the downloaded filename from the project title', function () {
    Pdf::shouldReceive('loadView')->andReturnSelf();
    Pdf::shouldReceive('setPaper')->andReturnSelf();
    Pdf::shouldReceive('download')
        ->once()
        ->with('plateforme-saas-metiers.pdf')
        ->andReturn(response('pdf', 200));

    $this->post(route('ideas.pdf'), validPdfRequestPayload([
        'title' => 'Plateforme SaaS Métiers',
        'application_type' => 'Web',
        'description' => 'Description.',
        'why_useful' => 'Utile.',
        'development_duration' => '3 mois',
        'recommended_stack' => 'Laravel',
        'business_model' => 'SaaS',
        'estimated_monthly_revenue' => '2000',
    ]));
});

it('uses a fallback filename when the title slug is empty', function () {
    Pdf::shouldReceive('loadView')->andReturnSelf();
    Pdf::shouldReceive('setPaper')->andReturnSelf();
    Pdf::shouldReceive('download')
        ->once()
        ->with('projet-workhelper.pdf')
        ->andReturn(response('pdf', 200));

    $this->post(route('ideas.pdf'), validPdfRequestPayload([
        'title' => '!!!',
        'application_type' => 'Web',
        'description' => 'Description.',
        'why_useful' => 'Utile.',
        'development_duration' => '3 mois',
        'recommended_stack' => 'Laravel',
        'business_model' => 'SaaS',
        'estimated_monthly_revenue' => '2000',
    ]));
});

it('validates required project fields', function () {
    $response = $this->from(route('home'))->post(route('ideas.pdf'), []);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors(['project']);
});

it('validates nested project fields', function () {
    $response = $this->from(route('home'))->post(route('ideas.pdf'), [
        'project' => ['title' => 'Sans les autres champs'],
    ]);

    $response
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors([
            'project.application_type',
            'project.description',
            'project.why_useful',
        ]);
});
