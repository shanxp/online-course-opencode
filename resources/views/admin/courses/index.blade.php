@extends('layouts.admin')

@section('title', __('messages.courses'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.courses') }}</h1>
        <a href="{{ route('admin.courses.create') }}" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
            {{ __('messages.create_course') }}
        </a>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        @if($courses->isEmpty())
            <x-empty-state :title="__('messages.no_courses_yet')" :description="__('messages.create_first_course_simple')"
                           :action="route('admin.courses.create')" :actionLabel="__('messages.create_course')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.title_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.creator') }}</th>
                        <th class="px-6 py-3">{{ __('messages.folders_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.status_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.homepage_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.sort_order_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.created_at_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($courses as $course)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.courses.show', $course) }}" class="text-primary-600 hover:text-primary-900 font-medium">
                                    {{ $course->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $course->creator->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $course->folders_count }}</td>
                            <td class="px-6 py-4">
                                @if($course->is_published)
                                    <span class="px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-full">{{ __('messages.published') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">{{ __('messages.draft') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($course->show_on_homepage)
                                    <span class="text-xs text-primary-600 font-medium">{{ __('messages.visible') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('messages.hidden') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('admin.courses.move-up', $course) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-0.5 text-gray-400 hover:text-gray-600 cursor-pointer" title="{{ __('messages.move_up') }}">
                                            <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.courses.move-down', $course) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-0.5 text-gray-400 hover:text-gray-600 cursor-pointer" title="{{ __('messages.move_down') }}">
                                            <svg class="w-3.5 h-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $course->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg border z-50">
                                        <a href="{{ route('admin.courses.show', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.view') }}</a>
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.edit') }}</a>
                                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}">
                                            @csrf @method('DELETE')
                                            <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.courses.destroy', $course) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 cursor-pointer">{{ __('messages.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $courses->links() }}
            </div>
        @endif
    </div>
@endsection
