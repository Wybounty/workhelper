<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class IdeaController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Ideas/Index');
    }
}
