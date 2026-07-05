<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreYouTubeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'youtube_id' => ['required', 'string', 'max:50'],
            'url' => ['required', 'url', 'max:500'],
            'course_id' => ['required', 'exists:courses,id'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
