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
        <form method="POST" action="{{ route('admin.media.update', $media) }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf @method('PUT')

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

            <div class="text-sm text-gray-500 space-y-1">
                <p><strong>{{ __('messages.course_col') }}:</strong> {{ $media->course->title }}</p>
                <p><strong>{{ __('messages.type_col') }}:</strong> {{ strtoupper($media->type) }}</p>
                <p><strong>{{ __('messages.size_col') }}:</strong> {{ round($media->size / 1024) }} {{ __('messages.kb') }}</p>
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