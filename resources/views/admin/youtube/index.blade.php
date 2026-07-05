@extends('layouts.admin')

@section('title', __('messages.youtube_management'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.youtube_management') }}</h1>
        <a href="{{ route('admin.youtube-videos.create') }}" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
            {{ __('messages.add_video') }}
        </a>
    </div>

    <div class="mt-4">
        <form method="GET">
            <select name="course_id" class="rounded-md border-gray-300 text-base px-4 py-3" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_courses') }}</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (string)$courseId === (string)$course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        @if($videos->isEmpty())
            <x-empty-state :title="__('messages.no_youtube_videos')" :description="__('messages.add_youtube_links')"
                           :action="route('admin.youtube-videos.create')" :actionLabel="__('messages.add_video')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.title_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.course_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($videos as $video)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">{{ $video->title }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $video->course->title }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ $video->url }}" target="_blank" class="text-primary-600 hover:text-primary-900 mr-3">{{ __('messages.watch') }}</a>
                                <a href="{{ route('admin.youtube-videos.edit', $video) }}" class="text-primary-600 hover:text-primary-900 mr-3">{{ __('messages.edit') }}</a>
                                <form method="POST" action="{{ route('admin.youtube-videos.destroy', $video) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.youtube-videos.destroy', $video) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
@endsection
