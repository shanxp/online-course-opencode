@extends('layouts.admin')

@section('title', __('messages.admin_dashboard'))

@section('page-content')
    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.admin_dashboard') }}</h1>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_courses') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_courses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('messages.published_courses') }}</p>
            <p class="mt-2 text-3xl font-bold text-green-600">{{ $stats['published_courses'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_users') }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-500">{{ __('messages.active_users') }}</p>
            <p class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['active_users'] }}</p>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-medium text-gray-900">{{ __('messages.recent_courses') }}</h2>
        </div>
        <div class="p-6">
            @if($recentCourses->isEmpty())
                <x-empty-state :title="__('messages.no_courses_yet')" :description="__('messages.create_first_course')"
                               :action="route('admin.courses.create')" :actionLabel="__('messages.create_course')" />
            @else
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-sm font-medium text-gray-500">
                            <th class="pb-3">{{ __('messages.id_col') }}</th>
                            <th class="pb-3">{{ __('messages.title_col') }}</th>
                            <th class="pb-3">{{ __('messages.created_by') }}</th>
                            <th class="pb-3">{{ __('messages.status_col') }}</th>
                            <th class="pb-3">{{ __('messages.date_col') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($recentCourses as $course)
                            <tr class="border-t">
                                <td class="py-3 text-gray-500">{{ $course->id }}</td>
                                <td class="py-3">
                                    <a href="{{ route('admin.courses.show', $course) }}" class="text-primary-600 hover:text-primary-900 font-medium">
                                        {{ $course->title }}
                                    </a>
                                </td>
                                <td class="py-3 text-gray-600">{{ $course->creator->name }}</td>
                                <td class="py-3">
                                    @if($course->is_published)
                                        <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">{{ __('messages.published') }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">{{ __('messages.draft') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-gray-500">{{ $course->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
