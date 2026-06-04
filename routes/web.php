<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\IdeaGenerationController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Occupations
Route::get('/occupations/import', [OccupationController::class, 'importOccupations'])->name('occupations.import');

// Ideas
Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');

Route::get('/ideas/generate', [IdeaController::class, 'generate'])->name('ideas.generate');
Route::post('/ideas/pdf', [IdeaController::class, 'downloadPdf'])->name('ideas.pdf');

// Historique des générations
Route::get('/generations', [IdeaGenerationController::class, 'index'])->name('generations.index');
Route::get('/generations/{generation}', [IdeaGenerationController::class, 'show'])->name('generations.show');