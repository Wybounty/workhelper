<?php

namespace App\Models;

use Database\Factories\OccupationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Occupation extends Model
{
    /** @use HasFactory<OccupationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'description'];

    
}
