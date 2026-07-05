@props(['media'])

<div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg border w-full">
    <audio controls class="w-full" preload="none">
        <source src="{{ route('media.stream', $media) }}" type="{{ $media->mime_type }}">
        {{ __('messages.browser_no_audio') }}
    </audio>
    <a href="{{ route('media.download', $media) }}"
       class="shrink-0 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
        {{ __('messages.download') }}
    </a>
</div>
