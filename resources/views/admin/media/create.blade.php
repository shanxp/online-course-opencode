@extends('layouts.admin')

@section('title', __('messages.upload_media'))

@section('page-content')
    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.upload_media') }}</h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">{{ __('messages.file') }}</label>
                <input type="file" name="file" id="file" required accept=".mp3,.pdf,audio/mpeg,application/pdf"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                @error('file') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

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
                        <option value="">{{ __('messages.no_folder_uncategorized') }}</option>
                    </select>
                @endif
                @error('folder_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.display_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.defaults_to_filename') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    {{ __('messages.upload') }}
                </button>
                <a href="{{ route('admin.media.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
