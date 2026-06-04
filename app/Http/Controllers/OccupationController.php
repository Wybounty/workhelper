<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use Illuminate\Support\Facades\DB;

class OccupationController extends Controller
{
    public function importOccupations()
    {
        $path = database_path('data/occupations.json');

        $occupations = json_decode(file_get_contents($path), true);

        $batch = [];

        foreach ($occupations as $occupation) {

            $batch[] = [
                'name' => $occupation['preferredLabel'],
                'description' => $occupation['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) === 500) {

                Occupation::upsert(
                    $batch,
                    ['name'],
                    ['description', 'updated_at']
                );

                $batch = [];
            }
        }

        if (! empty($batch)) {
            Occupation::upsert(
                $batch,
                ['name'],
                ['description', 'updated_at']
            );
        }

        $count = Occupation::count();

        return redirect()
            ->route('home')
            ->with('success', "Intégration ESCO terminée. {$count} métiers disponibles dans la base.");
    }
}