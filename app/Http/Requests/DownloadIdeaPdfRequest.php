<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DownloadIdeaPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project' => ['required', 'array'],
            'project.title' => ['required', 'string', 'max:500'],
            'project.application_type' => ['required', 'string', 'max:255'],
            'project.description' => ['required', 'string'],
            'project.why_useful' => ['required', 'string'],
            'project.development_duration' => ['required', 'string', 'max:255'],
            'project.recommended_stack' => ['required', 'string'],
            'project.business_model' => ['required', 'string', 'max:500'],
            'project.estimated_monthly_revenue' => ['required'],
            'occupation_name' => ['nullable', 'string', 'max:500'],
            'occupation_description' => ['nullable', 'string'],
        ];
    }
}
