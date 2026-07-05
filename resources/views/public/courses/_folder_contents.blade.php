@props(['folder'])

@foreach($folder->mediaFiles as $media)
    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
        <div class="flex items-center gap-3 w-full">
            @if($media->type === 'mp3')
                <svg class="w-8 h-8 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $media->name }}</p>
                    <p class="text-xs text-gray-500">{{ strtoupper($media->type) }} - {{ round($media->size / 1024) }} {{ __('messages.kb') }}</p>
                </div>
                @auth
                    <x-audio-player :media="$media" />
                @else
                    <a href="{{ route('login') }}" class="shrink-0 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-300 rounded-md hover:bg-primary-50">{{ __('messages.sign_in_to_view') }}</a>
                @endauth
            @else
                <svg class="w-8 h-8 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $media->name }}</p>
                    <p class="text-xs text-gray-500">{{ __('messages.pdf_dash', ['size' => round($media->size / 1024)]) }}</p>
                </div>
                @auth
                    <a href="{{ route('media.stream', $media) }}" target="_blank" class="shrink-0 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">{{ __('messages.view_pdf') }}</a>
                @else
                    <a href="{{ route('login') }}" class="shrink-0 px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-300 rounded-md hover:bg-primary-50">{{ __('messages.sign_in_to_view') }}</a>
                @endauth
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
        </div>
    </div>
@endforeach

@foreach($folder->children as $child)
    <div class="ml-6 border-l-2 border-gray-200 pl-4">
        <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-gray-700">{{ $child->name }}</h4>
        </div>
        <div class="space-y-3">
            @include('public.courses._folder_contents', ['folder' => $child])
        </div>
    </div>
@endforeach
