@extends('layouts.admin')

@section('title', __('messages.edit_course'))

@section('page-content')
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.courses.index') }}" class="hover:text-gray-700">{{ __('messages.courses') }}</a>
        <span>/</span>
        <span class="text-gray-900">{{ __('messages.edit') }}</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.edit_course') }}</h1>

    <div class="mt-6 max-w-2xl">
        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-6" x-data="{ slug: '{{ old('slug', $course->slug) }}', manualSlug: false }">
            @csrf @method('PUT')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('messages.title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title', $course->title) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3"
                       x-on:input="if (!manualSlug) slug = $el.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">{{ __('messages.slug') }}</label>
                <div class="relative">
                    <input type="text" name="slug" id="slug" x-model="slug"
                           @input="manualSlug = true"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.slug_auto_generated') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                <div id="editor" class="mt-1" style="min-height: 250px;" data-value="{{ old('description', $course->description) }}"></div>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if($course->thumbnail)
                <div class="flex items-center gap-4">
                    <img src="{{ Storage::url($course->thumbnail) }}" class="w-24 h-16 object-cover rounded">
                    <span class="text-sm text-gray-500">{{ __('messages.current_thumbnail') }}</span>
                    <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer ml-4">
                        <input type="checkbox" name="remove_thumbnail" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        {{ __('messages.remove_thumbnail') }}
                    </label>
                </div>
            @endif

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">{{ __('messages.thumbnail', ['size' => config('media.thumbnail_max_size')]) }}</label>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/jpg,image/jpeg,image/png,image/webp"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                @error('thumbnail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $course->is_published) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="is_published" class="text-sm text-gray-700">{{ __('messages.is_published') }}</label>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="show_on_homepage" value="0">
                <input type="checkbox" name="show_on_homepage" id="show_on_homepage" value="1" {{ old('show_on_homepage', $course->show_on_homepage) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="show_on_homepage" class="text-sm text-gray-700">{{ __('messages.show_on_homepage') }}</label>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                    {{ __('messages.update_course') }}
                </button>
                <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
