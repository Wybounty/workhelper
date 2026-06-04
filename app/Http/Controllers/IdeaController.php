<?php

namespace App\Http\Controllers;

use App\Http\Requests\DownloadIdeaPdfRequest;
use App\Models\IdeaGeneration;
use App\Models\Occupation;
use App\Services\IdeaGenerationStore;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;

class IdeaController extends Controller
{
    public function __construct(
        private readonly IdeaGenerationStore $generationStore,
    ) {}

    public function index(): RedirectResponse
    {
        $latest = IdeaGeneration::query()->latest()->first();

        if ($latest) {
            return redirect()->route('generations.show', $latest);
        }

        return redirect()->route('generations.index');
    }

    public function generate(): RedirectResponse
    {

        $max = Occupation::max('id');

        if (! $max) {
            return redirect()
                ->route('home')
                ->with('error', 'Aucun métier trouvé. Importez d\'abord les occupations.');
        }

        $occupation = Occupation::find(random_int(1, $max));

        if (! $occupation) {
            return redirect()
                ->route('home')
                ->with('error', 'Aucun métier trouvé. Importez d\'abord les occupations.');
        }

        $response = Http::timeout(120)->post(
            config('services.n8n.webhook'),
            [
                'brief' => [
                    'job' => $occupation->name,
                    'description' => $occupation->description,
                ],
            ]
        );

        if (! $response->successful()) {
            return redirect()
                ->route('home')
                ->with('error', 'Impossible de générer les idées pour le moment.');
        }

        $projects = $this->extractProjects($response->json());

        if (count($projects) === 0) {
            return redirect()
                ->route('home')
                ->with('error', 'Réponse invalide : aucun projet reçu.');
        }

        $generation = $this->generationStore->store(
            $occupation->name,
            $occupation->description,
            $projects,
        );

        return redirect()
            ->route('generations.show', $generation)
            ->with('success', 'Idées générées et enregistrées avec succès.');
    }

    public function downloadPdf(DownloadIdeaPdfRequest $request): HttpResponse
    {
        $validated = $request->validated();
        $project = $validated['project'];

        $filename = Str::slug($project['title']);
        $filename = $filename !== '' ? "{$filename}.pdf" : 'projet-workhelper.pdf';

        $pdf = Pdf::loadView('pdf.idea', [
            'project' => $project,
            'occupation_name' => $validated['occupation_name'] ?? null,
            'occupation_description' => $validated['occupation_description'] ?? null,
            'formatted_revenue' => $this->formatRevenue($project['estimated_monthly_revenue']),
            'generated_at' => now()->timezone(config('app.timezone'))->format('d/m/Y à H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    private function formatRevenue(mixed $value): string
    {
        $numeric = is_numeric($value)
            ? (float) $value
            : (float) preg_replace('/[^\d.,-]/', '', str_replace(',', '.', (string) $value));

        if ($numeric > 0) {
            return number_format($numeric, 0, ',', ' ').' € / mois';
        }

        return (string) $value;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractProjects(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        if (isset($data['projects']) && is_array($data['projects'])) {
            return array_values($data['projects']);
        }

        if (array_is_list($data) && isset($data[0]) && is_array($data[0])) {
            return $data;
        }

        return [];
    }
}
