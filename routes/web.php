<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OccupationController;
use App\Http\Controllers\IdeaController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Occupations
Route::get('/occupations/import', [OccupationController::class, 'importOccupations'])->name('occupations.import');

// Ideas
Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');