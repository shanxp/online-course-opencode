@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm sticky top-0 z-50 py-3">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-14">
                <a href="{{ route('home') }}" class="shrink-0">
                    <div style="max-width: 300px;" class="py-1">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', __('messages.course_cms')) }}" class="w-full h-auto block">
                    </div>
                </a>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-primary-600 hover:text-primary-800">{{ __('messages.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-white bg-primary-600 px-4 py-2 rounded-md hover:bg-primary-700 transition">{{ __('messages.sign_in') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($course->thumbnail)
                <img src="{{ Storage::url($course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-64 sm:h-80 object-cover">
            @else
                <div class="w-full h-64 sm:h-80 bg-gradient-to-br from-primary-100 to-purple-100 flex items-center justify-center">
                    <svg class="w-16 h-16 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $course->title }}</h1>

                @if($course->description)
                    <div class="mt-4 text-gray-600 leading-relaxed">{!! $course->description !!}</div>
                @endif

                @guest
                    <div class="mt-6 bg-primary-50 border border-primary-200 rounded-lg p-5">
                        <div class="flex items-start gap-4">
                            <svg class="w-6 h-6 text-primary-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-primary-800">{{ __('messages.sign_in_to_access') }}</p>
                                <p class="mt-1 text-sm text-primary-600">{{ __('messages.sign_in_to_access_description') }}</p>
                                <a href="{{ route('login') }}" class="mt-3 inline-block px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 transition">{{ __('messages.sign_in') }}</a>
                            </div>
                        </div>
                    </div>
                @endguest

            </div>
        </div>

    </div>

    <footer class="bg-gray-900 text-gray-400 mt-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
@endsection
