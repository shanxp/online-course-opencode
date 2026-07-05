@extends('layouts.admin')

@section('title', __('messages.folders'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.folders') }}</h1>
        <a href="{{ route('admin.folders.create') }}" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
            {{ __('messages.create_folder') }}
        </a>
    </div>

    <div class="mt-4">
        <form method="GET" class="flex gap-4">
            <select name="course_id" class="rounded-md border-gray-300 text-base px-4 py-3" onchange="this.form.submit()">
                <option value="">{{ __('messages.all_courses') }}</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (string)$courseId === (string)$course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        @if($folders->isEmpty())
            <x-empty-state :title="__('messages.no_folders_yet')" :description="__('messages.create_folder_to_organize')"
                           :action="route('admin.folders.create')" :actionLabel="__('messages.create_folder')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.name_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.course_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($folders as $folder)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">{{ $folder->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $folder->course->title }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.folders.edit', $folder) }}" class="text-primary-600 hover:text-primary-900 mr-3">{{ __('messages.edit') }}</a>
                                <form method="POST" action="{{ route('admin.folders.destroy', $folder) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.folders.destroy', $folder) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $folders->links() }}
            </div>
        @endif
    </div>
@endsection
