@extends('layouts.admin')

@section('title', __('messages.add_image'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.add_image') }}</h1>
        <a href="{{ route('admin.slideshow-images.index') }}" class="text-sm text-primary-600 hover:text-primary-800">{{ __('messages.back') }}</a>
    </div>

    <div class="mt-6 max-w-lg">
        <form method="POST" action="{{ route('admin.slideshow-images.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf

            <div>
                <label for="image_path" class="block text-sm font-medium text-gray-700">{{ __('messages.image_path') }}</label>
                <input type="text" name="image_path" id="image_path" value="{{ old('image_path') }}" required
                       placeholder="/path/to/image.jpg or https://example.com/image.jpg"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('image_path')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">{{ __('messages.sort_order') }}</label>
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order') }}" min="0"
                       class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">{{ __('messages.active') }}</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 cursor-pointer">{{ __('messages.save') }}</button>
                <a href="{{ route('admin.slideshow-images.index') }}" class="text-sm text-gray-600 hover:text-gray-800">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
