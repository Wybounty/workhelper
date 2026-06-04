<?php

use App\Models\GeneratedIdea;
use App\Models\IdeaGeneration;
use App\Models\Occupation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function occupationsJsonPath(): string
{
    return database_path('data/occupations.json');
}

/**
 * @param  list<array{preferredLabel: string, description?: string|null}>  $occupations
 */
function writeOccupationsJsonFile(array $occupations): void
{
    $directory = database_path('data');

    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    file_put_contents(occupationsJsonPath(), json_encode($occupations, JSON_THROW_ON_ERROR));
}

function removeOccupationsJsonFile(): void
{
    $path = occupationsJsonPath();

    if (file_exists($path)) {
        unlink($path);
    }
}

/**
 * @return array<string, mixed>
 */
function sampleProjectPayload(int $index = 1): array
{
    return [
        'title' => "Projet test {$index}",
        'application_type' => match ($index) {
            1 => 'Web',
            2 => 'Mobile',
            default => 'Automatisation',
        },
        'description' => "Description du projet {$index}.",
        'why_useful' => "Utile pour le métier — raison {$index}.",
        'development_duration' => "{$index} mois",
        'recommended_stack' => 'Laravel, Vue 3, Inertia',
        'business_model' => 'Abonnement SaaS',
        'estimated_monthly_revenue' => (string) (1500 * $index),
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function sampleProjectsPayload(): array
{
    return [
        sampleProjectPayload(1),
        sampleProjectPayload(2),
        sampleProjectPayload(3),
    ];
}

function fakeN8nWebhookSuccess(?array $projects = null): void
{
    Http::fake([
        config('services.n8n.webhook') => Http::response($projects ?? sampleProjectsPayload(), 200),
    ]);
}

function fakeN8nWebhookFailure(): void
{
    Http::fake([
        config('services.n8n.webhook') => Http::response(['error' => 'fail'], 500),
    ]);
}

/**
 * @return array<string, mixed>
 */
function validPdfRequestPayload(?array $project = null): array
{
    return [
        'project' => $project ?? sampleProjectPayload(),
        'occupation_name' => 'Développeur web',
        'occupation_description' => 'Description ESCO du métier.',
    ];
}

function createGenerationWithIdeas(int $ideasCount = 3): IdeaGeneration
{
    $generation = IdeaGeneration::factory()->create();

    GeneratedIdea::factory()
        ->count($ideasCount)
        ->for($generation, 'generation')
        ->create();

    return $generation->load('ideas');
}
