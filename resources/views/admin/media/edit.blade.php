@extends('layouts.admin')

@section('title', __('messages.edit_media'))

@section('page-content')
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.media.index') }}" class="hover:text-gray-700">{{ __('messages.media_management') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ $media->name }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.edit_media') }}</h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.media.update', $media) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf @method('PUT')

            @php
                $isPath = str_starts_with($media->path, '/');
                $currentSource = old('source', $isPath ? 'path' : 'upload');
            @endphp

            <div x-data="{ source: '{{ $currentSource }}' }">
                <div class="flex items-center gap-4 mb-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="source" value="upload" x-model="source" class="text-primary-600">
                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.upload_file') }}</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="source" value="path" x-model="source" class="text-primary-600">
                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.server_path') }}</span>
                    </label>
                </div>

                <div x-show="source === 'upload'">
                    <label for="file" class="block text-sm font-medium text-gray-700">{{ __('messages.file') }}</label>
                    <input type="file" name="file" id="file" accept=".mp3,.pdf,audio/mpeg,application/pdf"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.leave_blank_keep_current_file') }}</p>
                    @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div x-show="source === 'path'">
                    <label for="path" class="block text-sm font-medium text-gray-700">{{ __('messages.path') }}</label>
                    <input type="text" name="path" id="path" value="{{ old('path', $media->path) }}"
                           placeholder="/absolute/path/file.mp3 or relative/path/file.mp3"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    @error('path') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $media->name) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.folder') }} ({{ __('messages.optional') }})</label>
                <x-searchable-select name="folder_id" :options="$folderOptions" :placeholder="__('messages.search_folders')" displayField="display_name" :selected="$media->folder_id" />
                @error('folder_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="size" class="block text-sm font-medium text-gray-700">{{ __('messages.size_bytes') }}</label>
                <input type="number" name="size" id="size" min="0" value="{{ old('size', $media->size) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.leave_blank_keep_current_size') }}</p>
                @error('size') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="text-sm text-gray-500 space-y-1">
                <p><strong>{{ __('messages.course_col') }}:</strong> {{ $media->course->title }}</p>
                <p><strong>{{ __('messages.type_col') }}:</strong> {{ strtoupper($media->type) }}</p>
                <p><strong>{{ __('messages.size_col') }}:</strong> {{ round($media->size / 1024) }} {{ __('messages.kb') }}</p>
                <p><strong>{{ __('messages.path') }}:</strong> {{ $media->path }}</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                    {{ __('messages.update') }}
                </button>
                <a href="{{ route('admin.media.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
