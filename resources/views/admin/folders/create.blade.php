@extends('layouts.admin')

@section('title', __('messages.create_folder'))

@section('page-content')
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">{{ __('messages.courses') }}</a>
        <span>/</span>
        @if($selectedCourseId)
            <a href="{{ route('admin.courses.show', $selectedCourseId) }}" class="hover:text-gray-700">{{ __('messages.course') }}</a>
            <span>/</span>
        @endif
        <span class="text-gray-900">{{ __('messages.create_folder') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">
        @if($parentFolder)
            {{ __('messages.create_subfolder_in', ['parent' => $parentFolder->name]) }}
        @else
            {{ __('messages.create_folder') }}
        @endif
    </h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.folders.store') }}" class="bg-white rounded-lg shadow p-6 space-y-6">
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

            @if($parentFolder)
                <input type="hidden" name="parent_id" value="{{ $parentFolder->id }}">
            @endif

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.folder_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_sticky" value="0">
                <input type="checkbox" name="is_sticky" id="is_sticky" value="1" {{ old('is_sticky') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="is_sticky" class="text-sm text-gray-700">{{ __('messages.sticky') }}</label>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                    {{ __('messages.create_folder') }}
                </button>
                <a href="{{ $selectedCourseId ? route('admin.courses.show', $selectedCourseId) : route('admin.courses.index') }}"
                   class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
