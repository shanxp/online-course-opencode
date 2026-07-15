<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required_without:path', 'nullable', 'file', 'mimes:mp3,pdf', 'max:' . config('media.max_upload_size')],
            'path' => ['required_without:file', 'nullable', 'string', 'max:1024'],
            'name' => ['nullable', 'string', 'max:255'],
            'course_id' => ['required', 'exists:courses,id'],
            'folder_id' => ['nullable', Rule::exists('folders', 'id')->where('course_id', $this->input('course_id'))],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_without' => __('messages.msg_file_or_path_required'),
            'path.required_without' => __('messages.msg_file_or_path_required'),
        ];
    }
}
