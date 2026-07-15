@extends('layouts.user')

@section('title', 'My Courses')

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.my_courses') }}</h1>
        <select onchange="window.location = '{{ route('dashboard') }}?sort=' + this.value"
                class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2">
            <option value="default" {{ ($sort ?? '') === 'default' ? 'selected' : '' }}>{{ __('messages.sort_default') }}</option>
            <option value="title_asc" {{ ($sort ?? '') === 'title_asc' ? 'selected' : '' }}>{{ __('messages.sort_title_asc') }}</option>
            <option value="title_desc" {{ ($sort ?? '') === 'title_desc' ? 'selected' : '' }}>{{ __('messages.sort_title_desc') }}</option>
            <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>{{ __('messages.sort_newest') }}</option>
            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
        </select>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($courses as $course)
            <a href="{{ route('courses.show', $course) }}" class="block bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition group">
                @if($course->thumbnail)
                    <img src="{{ Storage::url($course->thumbnail) }}" class="w-full h-48 sm:h-56 object-cover">
                @else
                    <div class="w-full h-48 sm:h-56 bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center">
                        <span class="text-3xl font-bold text-white">{{ substr($course->title, 0, 2) }}</span>
                    </div>
                @endif
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary-600 transition">{{ $course->title }}</h3>
                    @if($course->description)
                        <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ strip_tags($course->description) }}</p>
                    @endif
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-medium text-primary-600 group-hover:text-primary-800 transition">{!! __('messages.browse_content') !!}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <x-empty-state title="{{ __('messages.no_courses_assigned') }}" description="{{ __('messages.contact_admin') }}" />
            </div>
        @endforelse
    </div>

@endsection
