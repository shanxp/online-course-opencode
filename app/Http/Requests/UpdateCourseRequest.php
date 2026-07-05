<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug,' . $this->route('course')?->id],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . config('media.thumbnail_max_size')],
            'is_published' => ['boolean'],
            'show_on_homepage' => ['boolean'],
            'remove_thumbnail' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
