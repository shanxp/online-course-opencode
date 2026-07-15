<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', __('messages.course_cms')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div x-data="{ mobileOpen: false }" class="min-h-screen flex flex-col">
        <nav class="bg-white shadow-sm sticky top-0 z-50 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ route('home') }}" class="shrink-0">
                        <div style="max-width: 300px;" class="py-2">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', __('messages.course_cms')) }}" class="w-full h-auto block">
                        </div>
                    </a>
                    <div class="hidden md:flex items-center gap-6">
                        <a href="#courses" class="text-sm text-gray-600 hover:text-primary-600 transition">{{ __('messages.home') }}</a>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4a4 4 0 100 8 4 4 0 000-8z"/>
                                </svg>
                                <span>{{ strtoupper(app()->getLocale()) }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg border z-50">
                                <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'en' ? 'text-primary-600 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">EN</a>
                                <a href="{{ route('locale.switch', 'de') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'de' ? 'text-primary-600 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">DE</a>
                            </div>
                        </div>
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="text-sm font-medium text-white bg-primary-600 px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                {{ __('messages.dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="text-sm font-medium text-white bg-primary-600 px-4 py-2 rounded-md hover:bg-primary-700 transition">
                                {{ __('messages.login') }}
                            </a>
                        @endauth
                    </div>

                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  :d="mobileOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="mobileOpen" x-cloak class="md:hidden border-t bg-white">
                <div class="px-4 py-3 space-y-2">
                    <a @click="mobileOpen = false" href="#courses"
                       class="block px-3 py-2 text-sm text-gray-600 hover:text-primary-600 rounded-md hover:bg-gray-50">{{ __('messages.home') }}</a>
                    @auth
                        <a @click="mobileOpen = false" href="{{ route('dashboard') }}"
                           class="block px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 text-center">
                            {{ __('messages.dashboard') }}
                        </a>
                    @else
                        <a @click="mobileOpen = false" href="{{ route('login') }}"
                           class="block px-3 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 text-center">
                            {{ __('messages.login') }}
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        @php
            $slidePaths = $slideshowImages->pluck('image_path')->toArray();
            $slideCount = count($slidePaths);
        @endphp
        <section x-data="{
                total: {{ $slideCount > 0 ? $slideCount : 1 }},
                current: 0,
                timer: null,
                init() {
                    this.timer = setInterval(() => {
                        this.current = (this.current + 1) % this.total;
                    }, 5000);
                },
                destroy() {
                    clearInterval(this.timer);
                }
            }" class="relative overflow-hidden text-white">

            <div class="absolute inset-0 bg-gray-900">
                @forelse($slidePaths as $i => $path)
                    <img x-show="current === {{ $i }}" src="{{ $path }}"
                         class="absolute inset-0 w-full h-full object-cover" alt="">
                @empty
                    <img src="https://picsum.photos/seed/default/1600/700"
                         class="absolute inset-0 w-full h-full object-cover" alt="">
                @endforelse
            </div>

            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/20 via-gray-900/10 to-transparent"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36">
                <div class="max-w-3xl">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight">
                        {{ __('messages.hero_title') }}
                    </h1>
                    <p class="mt-4 text-base sm:text-lg text-gray-200 leading-relaxed max-w-2xl">
                        {{ __('messages.hero_subtitle') }}
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="#courses"
                           class="inline-flex items-center justify-center px-6 py-3 bg-white text-gray-900 font-medium rounded-md hover:bg-gray-100 transition shadow-md">
                            {{ __('messages.browse_courses') }}
                        </a>
                        @guest
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center px-6 py-3 border border-white/50 text-white font-medium rounded-md hover:bg-white/10 transition">
                                {{ __('messages.sign_in') }}
                            </a>
                        @endguest
                    </div>
                </div>
            </div>

            <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-10">
                @for($i = 0; $i < max($slideCount, 1); $i++)
                    <button @click="current = {{ $i }}"
                            :class="current === {{ $i }} ? 'bg-white' : 'bg-white/40'"
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300 hover:bg-white/70"></button>
                @endfor
            </div>

        </section>

        <section id="courses" class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 w-full">
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('messages.our_courses') }}</h2>
                <p class="mt-2 text-gray-600">{{ __('messages.our_courses_subtitle') }}</p>
            </div>

            @if($courses->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <p>{{ __('messages.no_courses_available') }}</p>
                </div>
            @else
                <div class="flex justify-end mb-4">
                    <select onchange="window.location = '{{ route('home') }}?sort=' + this.value"
                            class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2">
                        <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>{{ __('messages.sort_default') }}</option>
                        <option value="title_asc" {{ $sort === 'title_asc' ? 'selected' : '' }}>{{ __('messages.sort_title_asc') }}</option>
                        <option value="title_desc" {{ $sort === 'title_desc' ? 'selected' : '' }}>{{ __('messages.sort_title_desc') }}</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>{{ __('messages.sort_newest') }}</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($courses as $course)
                        <a href="{{ route('courses.public.show', ['slug' => $course->slug]) }}"
                           class="block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                            @if($course->thumbnail)
                                <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}"
                                     class="w-full h-48 sm:h-56 object-cover">
                            @else
                                <div class="w-full h-48 sm:h-56 bg-gradient-to-br from-primary-100 to-purple-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-primary-600 transition">{{ $course->title }}</h3>
                                @if($course->description)
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ strip_tags($course->description) }}</p>
                                @endif
                                <div class="mt-4 flex items-center justify-between">
                                    <span class="text-sm font-medium text-primary-600 group-hover:text-primary-800 transition">{{ __('messages.view_content') }} &rarr;</span>
                                    @can('update', $course)
                                        <span onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('admin.courses.edit', $course) }}'"
                                              class="text-sm font-medium text-amber-600 hover:text-amber-800 cursor-pointer">{{ __('messages.edit') }}</span>
                                    @endcan
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <footer class="bg-gray-900 text-gray-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm">&copy; {{ date('Y') }} {{ config('app.name', __('messages.course_cms')) }}. {{ __('messages.all_rights_reserved') }}</p>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-400 hover:text-white transition">{{ __('messages.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white transition">{{ __('messages.login') }}</a>
                    @endauth
                </div>
            </div>
        </footer>
    </div>

    <x-toast />
</body>
</html>
