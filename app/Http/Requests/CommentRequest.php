<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'max:2000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ];

        // Only require these for creating
        if ($this->isMethod('post')) {
            $rules['commentable_type'] = ['required', 'in:post,article'];
            $rules['commentable_id'] = ['required', 'integer'];
            $rules['parent_id'] = ['nullable', 'exists:comments,id'];
        }

        return $rules;
    }
}
