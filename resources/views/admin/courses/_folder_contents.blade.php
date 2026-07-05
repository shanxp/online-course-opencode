@props(['folder'])

@foreach($folder->mediaFiles as $media)
    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
        <div class="flex items-center gap-3 w-full">
            @if($media->type === 'mp3')
                <x-audio-player :media="$media" />
            @else
                <svg class="w-8 h-8 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $media->name }}</p>
                    <p class="text-xs text-gray-500">{{ __('messages.pdf_dash', ['size' => round($media->size / 1024)]) }}</p>
                </div>
                <a href="{{ route('media.stream', $media) }}" target="_blank"
                   class="shrink-0 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">{{ __('messages.view_pdf') }}</a>
                <form method="POST" action="{{ route('admin.media.destroy', $media) }}" class="inline shrink-0">
                    @csrf @method('DELETE')
                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.media.destroy', $media) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
                </form>
            @endif
        </div>
    </div>
@endforeach

@foreach($folder->youtubeVideos as $video)
    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
        <div class="flex items-center gap-3">
            <svg class="w-8 h-8 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ $video->title }}</p>
                <a href="{{ $video->url }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-900">{{ __('messages.watch_on_youtube') }}</a>
            </div>
            <form method="POST" action="{{ route('admin.youtube-videos.destroy', $video) }}" class="inline">
                @csrf @method('DELETE')
                <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.youtube-videos.destroy', $video) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
            </form>
        </div>
    </div>
@endforeach

@foreach($folder->children as $child)
    <div class="ml-6 border-l-2 border-gray-200 pl-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h4 class="text-sm font-medium text-gray-700">{{ $child->name }}</h4>
                <p class="text-xs text-gray-400">{{ $child->created_at->format('M d, Y H:i') }} &middot; {{ __('messages.updated_at_col') }}: {{ $child->updated_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-1">
                <form method="POST" action="{{ route('admin.folders.move-up', $child) }}" class="inline">
                    @csrf
                    <button class="p-0.5 text-gray-400 hover:text-gray-600" title="{{ __('messages.move_up') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.folders.move-down', $child) }}" class="inline">
                    @csrf
                    <button class="p-0.5 text-gray-400 hover:text-gray-600" title="{{ __('messages.move_down') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </form>
                <a href="{{ route('admin.media.create', ['course_id' => $child->course_id, 'folder_id' => $child->id]) }}"
                   class="text-xs text-green-600 hover:text-green-800 ml-1">{{ __('messages.add_file') }}</a>
                <a href="{{ route('admin.folders.edit', $child) }}" class="text-xs text-gray-500 hover:text-gray-700 ml-1">{{ __('messages.edit') }}</a>
                <a href="{{ route('admin.folders.create', ['course_id' => $child->course_id, 'parent_id' => $child->id]) }}"
                   class="text-xs text-primary-600 hover:text-primary-900 ml-1">{{ __('messages.add_sub') }}</a>
                <form method="POST" action="{{ route('admin.folders.destroy', $child) }}" class="inline ml-1">
                    @csrf @method('DELETE')
                    <button type="button" @click.prevent="$dispatch('confirm-open', { action: '{{ route('admin.folders.destroy', $child) }}', method: 'DELETE', message: '{{ __('messages.confirm_delete_folder') }}' })" class="text-xs text-red-600 hover:text-red-800 cursor-pointer">{{ __('messages.delete') }}</button>
                </form>
            </div>
        </div>
        <div class="space-y-3">
            @include('admin.courses._folder_contents', ['folder' => $child])
        </div>
    </div>
@endforeach
