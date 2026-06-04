<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $dataFile = database_path('data/occupations.json');

        return Inertia::render('Home', [
            'occupations_count' => Occupation::count(),
            'esco_file' => [
                'present' => file_exists($dataFile),
                'name' => 'occupations.json',
                'relative_path' => 'database/data/occupations.json',
            ],
        ]);
    }
}
