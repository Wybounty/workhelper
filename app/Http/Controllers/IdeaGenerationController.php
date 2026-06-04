<?php

namespace App\Http\Controllers;

use App\Models\IdeaGeneration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IdeaGenerationController extends Controller
{
    public function index(Request $request): Response
    {
        $generations = IdeaGeneration::query()
            ->withCount('ideas')
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (IdeaGeneration $generation) => [
                'id' => $generation->id,
                'occupation_name' => $generation->occupation_name,
                'occupation_description' => $generation->occupation_description,
                'ideas_count' => $generation->ideas_count,
                'created_at' => $generation->created_at->timezone(config('app.timezone'))->format('d/m/Y à H:i'),
            ]);

        return Inertia::render('Generations/Index', [
            'generations' => $generations,
        ]);
    }

    public function show(IdeaGeneration $generation): Response
    {
        $generation->load('ideas');

        return Inertia::render('Generations/Show', [
            'generation' => [
                'id' => $generation->id,
                'occupation_name' => $generation->occupation_name,
                'occupation_description' => $generation->occupation_description,
                'created_at' => $generation->created_at->timezone(config('app.timezone'))->format('d/m/Y à H:i'),
                'projects' => $generation->ideas->map(fn ($idea) => [
                    'title' => $idea->title,
                    'application_type' => $idea->application_type,
                    'description' => $idea->description,
                    'why_useful' => $idea->why_useful,
                    'development_duration' => $idea->development_duration,
                    'recommended_stack' => $idea->recommended_stack,
                    'business_model' => $idea->business_model,
                    'estimated_monthly_revenue' => $idea->estimated_monthly_revenue,
                ])->values()->all(),
            ],
        ]);
    }
}
