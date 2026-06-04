<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdeaGeneration extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'occupation_name',
        'occupation_description',
    ];

    public function ideas(): HasMany
    {
        return $this->hasMany(GeneratedIdea::class, 'generation_id');
    }
}
