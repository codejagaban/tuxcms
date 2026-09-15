<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $job = $this->route('job');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('job_posts', 'slug')->ignore($job)],
            'location' => ['nullable', 'string', 'max:255'],
            'job_type' => ['nullable', 'string', 'max:100'],
            'hours' => ['nullable', 'string', 'max:100'],
            'salary' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'summary' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'array'],
            'responsibilities.*' => ['string'],
            'essential' => ['nullable', 'array'],
            'essential.*' => ['string'],
            'desirable' => ['nullable', 'array'],
            'desirable.*' => ['string'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string'],
            'application_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'published_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
        ];
    }
}
