@props(['folder', 'accessibleFolderIds' => null])

@php $canSee = fn($f) => $accessibleFolderIds === null || in_array($f->id, $accessibleFolderIds); @endphp

@foreach($folder->mergedContents() as $content)
    @php $item = $content['item']; $kind = $content['kind']; @endphp

    @if($kind === 'media' && $item->type === 'mp3')
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
            <div class="flex items-center gap-3 w-full">
                <x-audio-player :media="$item" />
            </div>
        </div>
    @elseif($kind === 'media')
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
            <div class="flex items-center gap-3 w-full">
                <svg class="w-8 h-8 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                    <p class="text-xs text-gray-500">{{ __('messages.pdf_dash', ['size' => round($item->size / 1024)]) }}</p>
                </div>
                <a href="{{ route('media.download', $item) }}"
                   class="shrink-0 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">{{ __('messages.download') }}</a>
            </div>
        </div>
    @elseif($kind === 'youtube')
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
            <div class="flex items-center gap-3 w-full">
                <svg class="w-8 h-8 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->title }}</p>
                    <p class="text-xs text-gray-400">{{ $item->created_at->format('M d, Y H:i') }}</p>
                </div>
                <a href="{{ $item->url }}" target="_blank" class="shrink-0 text-xs text-primary-600 hover:text-primary-900">{{ __('messages.watch_on_youtube') }}</a>
            </div>
        </div>
    @endif
@endforeach

@foreach($folder->children->filter($canSee) as $child)
    <div class="ml-6 border-l-2 border-gray-200 pl-4">
        <div class="flex items-center justify-between mb-2">
            <div>
                    <h4 class="text-base font-bold text-gray-900">{{ $child->name }}</h4>
                <p class="text-xs text-gray-400">{{ $child->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <div class="space-y-3">
            @include('user.courses._folder_contents', ['folder' => $child, 'accessibleFolderIds' => $accessibleFolderIds])
        </div>
    </div>
@endforeach