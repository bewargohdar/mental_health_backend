<?php

namespace App\Http\Requests;

use App\Enums\ContentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'category' => ['nullable', Rule::enum(ContentCategory::class)],
            'is_anonymous' => ['nullable', 'boolean'],
        ];
    }
}
