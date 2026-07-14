@extends('layouts.admin')

@section('title', __('messages.add_youtube'))

@section('page-content')
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.youtube-videos.index') }}" class="hover:text-gray-700">{{ __('messages.youtube_management') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('messages.add') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.add_youtube') }}</h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.youtube-videos.store') }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">{{ __('messages.course') }}</label>
                <select name="course_id" id="course_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    <option value="">{{ __('messages.select_course') }}</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $selectedCourseId == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
                @error('course_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.folder') }} ({{ __('messages.optional') }})</label>
                @if($selectedCourseId && $folderOptions)
                    <x-searchable-select name="folder_id" :options="$folderOptions" :placeholder="__('messages.search_folders')" displayField="display_name" />
                @else
                    <select name="folder_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                        <option value="">{{ __('messages.select_course_first') }}</option>
                    </select>
                @endif
                @error('folder_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('messages.title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="youtube_id" class="block text-sm font-medium text-gray-700">{{ __('messages.youtube_id') }}</label>
                <input type="text" name="youtube_id" id="youtube_id" value="{{ old('youtube_id') }}"
                       placeholder="{{ __('messages.youtube_id_placeholder') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.youtube_id_optional') }}</p>
                @error('youtube_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-gray-700">{{ __('messages.youtube_url') }}</label>
                <input type="url" name="url" id="url" value="{{ old('url') }}" required
                       placeholder="{{ __('messages.youtube_url_placeholder') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    {{ __('messages.add_video') }}
                </button>
                <a href="{{ route('admin.youtube-videos.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
