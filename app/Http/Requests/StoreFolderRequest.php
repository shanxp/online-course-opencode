<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'course_id' => ['required', 'exists:courses,id'],
            'parent_id' => ['nullable', 'exists:folders,id'],
            'sort_order' => ['integer', 'min:0'],
            'is_sticky' => ['nullable', 'boolean'],
        ];
    }
}
