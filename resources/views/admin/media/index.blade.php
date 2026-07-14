@extends('layouts.admin')

@section('title', __('messages.media_management'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.media_management') }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.media.create') }}" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                {{ __('messages.upload_file') }}
            </a>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap gap-4 items-end">
        <form method="GET" class="flex gap-4">
            <select name="course_id" class="rounded-md border-gray-300 text-base px-4 py-3" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_courses') }}</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (string)$courseId === (string)$course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded-md border-gray-300 text-base px-4 py-3" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_types') }}</option>
                <option value="mp3" {{ $type === 'mp3' ? 'selected' : '' }}>{{ __('messages.mp3_audio') }}</option>
                <option value="pdf" {{ $type === 'pdf' ? 'selected' : '' }}>{{ __('messages.pdf_document') }}</option>
            </select>
        </form>

        <form method="POST" action="{{ route('admin.media.sync') }}" class="flex gap-2 items-end">
            @csrf
            <select name="course_id" required class="rounded-md border-gray-300 text-base px-4 py-3">
                <option value="">{{ __('messages.sync_to_course') }}</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700">
                {{ __('messages.sync_media') }}
            </button>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        @if($media->isEmpty())
            <x-empty-state :title="__('messages.no_media_files')" :description="__('messages.upload_mp3_pdf')"
                           :action="route('admin.media.create')" :actionLabel="__('messages.upload_file')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.name_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.type_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.course_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.size_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($media as $file)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">{{ $file->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $file->type === 'mp3' ? 'bg-primary-100 text-primary-700' : 'bg-red-100 text-red-700' }}">
                                    {{ strtoupper($file->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $file->course->title }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ round($file->size / 1024) }} {{ __('messages.kb') }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.media.edit', $file) }}" class="text-gray-500 hover:text-gray-700 mr-3">{{ __('messages.edit') }}</a>
                                <a href="{{ route('media.download', $file) }}" class="text-primary-600 hover:text-primary-900 mr-3">{{ __('messages.download') }}</a>
                                <form method="POST" action="{{ route('admin.media.destroy', $file) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.media.destroy', $file) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $media->links() }}
            </div>
        @endif
    </div>
@endsection
