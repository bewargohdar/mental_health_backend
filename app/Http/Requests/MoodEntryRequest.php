<?php

namespace App\Http\Requests;

use App\Enums\MoodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoodEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mood_type' => ['required', Rule::enum(MoodType::class)],
            'intensity' => ['required', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'factors' => ['nullable', 'array'],
            'factors.*' => ['string'],
            'activities' => ['nullable', 'array'],
            'activities.*' => ['string'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'is_private' => ['nullable', 'boolean'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
