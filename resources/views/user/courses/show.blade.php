@extends('layouts.user')

@section('title', $course->title)

@section('page-content')
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-gray-700">{{ __('messages.my_courses_link') }}</a>
                <span>/</span>
                <span class="text-gray-900">{{ $course->title }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $course->title }}</h1>
        </div>
    </div>

    @if($course->thumbnail || $course->description)
        <div class="mt-4 flex flex-col sm:flex-row gap-6">
            @if($course->thumbnail)
                <div class="shrink-0">
                    <img src="{{ Storage::url($course->thumbnail) }}" class="w-48 h-32 object-cover rounded-lg">
                </div>
            @endif
            @if($course->description)
                <div class="text-gray-600 leading-relaxed">{!! $course->description !!}</div>
            @endif
        </div>
    @endif

    @if($accessibleFolderIds === null && ($course->mediaFiles->whereNull('folder_id')->isNotEmpty() || $course->youtubeVideos->whereNull('folder_id')->isNotEmpty()))
        <div class="mt-4 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">{{ __('messages.uncategorized_content') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($course->mediaFiles->whereNull('folder_id') as $media)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                        <div class="flex items-center gap-3">
                            @if($media->type === 'mp3')
                                <svg class="w-8 h-8 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $media->name }}</p>
                                    <p class="text-xs text-gray-500">{{ strtoupper($media->type) }} - {{ round($media->size / 1024) }} {{ __('messages.kb') }}</p>
                                </div>
                                <x-audio-player :media="$media" />
                            @else
                                <svg class="w-8 h-8 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $media->name }}</p>
                                    <p class="text-xs text-gray-500">{{ __('messages.pdf_dash', ['size' => round($media->size / 1024)]) }}</p>
                                </div>
                                <a href="{{ route('media.download', $media) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">{{ __('messages.download') }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach

                @foreach($course->youtubeVideos->whereNull('folder_id') as $video)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816zM9 16V8l8 4-8 4z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $video->title }}</p>
                                <a href="{{ $video->url }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-900">{{ __('messages.watch_on_youtube') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-4">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="{{ __('messages.search') }} {{ __('messages.folders') }}..."
                   class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3 w-72">
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 cursor-pointer">{{ __('messages.filter') }}</button>
            @if($search)
                <a href="{{ route('courses.show', $course) }}" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900 self-center">{{ __('messages.clear') }}</a>
            @endif
        </form>
    </div>

    @php $showFolder = fn($f) => $accessibleFolderIds === null || in_array($f->id, $accessibleFolderIds); @endphp

    <div class="mt-4 space-y-3">
        @forelse($folders as $folder)
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                <div @click="open = !open" class="px-6 py-4 flex items-center gap-3 cursor-pointer hover:bg-gray-50 transition">
                    <svg x-show="!open" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <svg x-show="open" class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $folder->name }}</h3>
                        <p class="text-xs text-gray-400">{{ $folder->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <div x-show="open" x-collapse class="border-t">
                    <div class="p-6 space-y-4">
                        @include('user.courses._folder_contents', ['folder' => $folder, 'accessibleFolderIds' => $accessibleFolderIds])
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                {{ __('messages.no_content_in_course') }}
            </div>
        @endforelse

        <div class="px-6 py-4">
            {{ $folders->links() }}
        </div>
    </div>
@endsection
