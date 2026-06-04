<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedIdea extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'generation_id',
        'title',
        'application_type',
        'description',
        'why_useful',
        'development_duration',
        'recommended_stack',
        'business_model',
        'estimated_monthly_revenue',
    ];

    public function generation(): BelongsTo
    {
        return $this->belongsTo(IdeaGeneration::class, 'generation_id');
    }
}
