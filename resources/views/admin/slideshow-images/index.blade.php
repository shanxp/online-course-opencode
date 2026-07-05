@extends('layouts.admin')

@section('title', __('messages.slideshow_management'))

@section('page-content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.slideshow_management') }}</h1>
        <a href="{{ route('admin.slideshow-images.create') }}" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
            {{ __('messages.add_image') }}
        </a>
    </div>

    <div class="mt-6 bg-white rounded-lg shadow">
        @if($images->isEmpty())
            <x-empty-state :title="__('messages.no_images')" :description="__('messages.add_first_image')" :action="route('admin.slideshow-images.create')" :actionLabel="__('messages.add_image')" />
        @else
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm font-medium text-gray-500 border-b">
                        <th class="px-6 py-3">{{ __('messages.image_path') }}</th>
                        <th class="px-6 py-3">{{ __('messages.sort_order') }}</th>
                        <th class="px-6 py-3">{{ __('messages.status_col') }}</th>
                        <th class="px-6 py-3">{{ __('messages.actions_col') }}</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($images as $image)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium max-w-xs truncate">{{ $image->image_path }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $image->sort_order }}</td>
                            <td class="px-6 py-4">
                                @if($image->is_active)
                                    <span class="text-green-600 font-medium">{{ __('messages.active') }}</span>
                                @else
                                    <span class="text-red-600 font-medium">{{ __('messages.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" class="text-primary-600 hover:text-primary-900 text-sm cursor-pointer">{{ __('messages.actions_col') }}</button>
                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-36 bg-white rounded-md shadow-lg border z-50">
                                        <a href="{{ route('admin.slideshow-images.edit', $image) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('messages.edit') }}</a>
                                        <form method="POST" action="{{ route('admin.slideshow-images.destroy', $image) }}">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                    @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.slideshow-images.destroy', $image) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete_image') }}' })"
                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 cursor-pointer">{{ __('messages.delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
