<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow both authenticated users and guests (for panic reports)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'campus' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'location' => ['required', 'string', 'max:255'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high'])],
            'anonymous' => ['sometimes', 'boolean'],
            'media' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,gif,mp4,mov,avi,webp',
                'max:20480', // 20MB max file size
            ],
            'guest_name' => ['nullable', 'required_without:user_id', 'string', 'max:255'],
            'guest_email' => ['nullable', 'required_without:user_id', 'email', 'max:255'],
        ];
    }

    /**
     * Configure the rate limiter for the request.
     */
    protected function prepareForValidation(): void
    {
        // Rate limit the request to prevent abuse
        $key = 'report-submission:' . ($this->user() ? $this->user()->id : $this->ip());
        
        // Allow 5 submissions per minute for authenticated users, 2 for guests
        $maxAttempts = $this->user() ? 5 : 2;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Too many reports submitted. Please wait {$seconds} seconds before trying again.");
        }
        
        RateLimiter::hit($key, 60); // 1 minute decay
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'campus.required' => 'Please specify a campus or location area.',
            'description.required' => 'Please provide a description of the incident.',
            'description.min' => 'The description must be at least 10 characters long.',
            'location.required' => 'Please provide the location of the incident.',
            'severity.required' => 'Please indicate the severity of the incident.',
            'severity.in' => 'Severity must be low, medium, or high.',
            'media.max' => 'The uploaded file may not be larger than 20MB.',
            'media.mimes' => 'The file must be an image (JPEG, PNG, GIF) or video (MP4, MOV, AVI).',
            'guest_name.required_without' => 'Please provide your name for anonymous reports.',
            'guest_email.required_without' => 'Please provide your email for anonymous reports.',
            'guest_email.email' => 'Please provide a valid email address.',
        ];
    }
    
    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'campus' => 'campus/area',
            'location' => 'incident location',
            'media' => 'photo/video evidence',
        ];
    }
}

