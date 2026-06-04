<?php

namespace App\Services;

use App\Models\IdeaGeneration;
use Illuminate\Support\Facades\DB;

class IdeaGenerationStore
{
    /**
     * @param  list<array<string, mixed>>  $projects
     */
    public function store(
        string $occupationName,
        ?string $occupationDescription,
        array $projects,
    ): IdeaGeneration {
        return DB::transaction(function () use ($occupationName, $occupationDescription, $projects): IdeaGeneration {
            $generation = IdeaGeneration::query()->create([
                'occupation_name' => $occupationName,
                'occupation_description' => $occupationDescription,
            ]);

            foreach (array_slice($projects, 0, 3) as $project) {
                $generation->ideas()->create($this->normalizeIdea($project));
            }

            return $generation->load('ideas');
        });
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array<string, string>
     */
    private function normalizeIdea(array $project): array
    {
        return [
            'title' => (string) ($project['title'] ?? 'Sans titre'),
            'application_type' => (string) ($project['application_type'] ?? '—'),
            'description' => (string) ($project['description'] ?? ''),
            'why_useful' => (string) ($project['why_useful'] ?? ''),
            'development_duration' => (string) ($project['development_duration'] ?? '—'),
            'recommended_stack' => (string) ($project['recommended_stack'] ?? ''),
            'business_model' => (string) ($project['business_model'] ?? '—'),
            'estimated_monthly_revenue' => (string) ($project['estimated_monthly_revenue'] ?? '—'),
        ];
    }
}
