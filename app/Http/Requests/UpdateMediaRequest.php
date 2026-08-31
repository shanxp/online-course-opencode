<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'folder_id' => ['nullable', Rule::exists('folders', 'id')->where('course_id', $this->route('media')->course_id)],
            'source' => ['nullable', 'in:upload,path'],
            'file' => ['nullable', 'file', 'mimes:mp3,pdf', 'max:' . config('media.max_upload_size')],
            'path' => ['nullable', 'string', 'max:1024'],
            'size' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
