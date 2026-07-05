@extends('layouts.admin')

@section('title', __('messages.edit_youtube'))

@section('page-content')
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.youtube-videos.index') }}" class="hover:text-gray-700">{{ __('messages.youtube_management') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('messages.edit') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.edit_youtube') }}</h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.youtube-videos.update', $youtubeVideo) }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf @method('PUT')

            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">{{ __('messages.course') }}</label>
                <select name="course_id" id="course_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $youtubeVideo->course_id == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('messages.title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title', $youtubeVideo->title) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
            </div>

            <div>
                <label for="youtube_id" class="block text-sm font-medium text-gray-700">{{ __('messages.youtube_id') }}</label>
                <input type="text" name="youtube_id" id="youtube_id" value="{{ old('youtube_id', $youtubeVideo->youtube_id) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
            </div>

            <div>
                <label for="url" class="block text-sm font-medium text-gray-700">{{ __('messages.youtube_url') }}</label>
                <input type="url" name="url" id="url" value="{{ old('url', $youtubeVideo->url) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">{{ old('description', $youtubeVideo->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    {{ __('messages.update_video') }}
                </button>
                <a href="{{ route('admin.youtube-videos.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
