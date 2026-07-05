@extends('layouts.app')

@section('title', __('messages.login'))

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="flex items-center justify-between mb-8">
                <a href="{{ route('home') }}" class="block hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-16">
                </a>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m0 4a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-24 bg-white rounded-md shadow-lg border z-50">
                        <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'en' ? 'text-primary-600 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">{{ __('messages.en') }}</a>
                        <a href="{{ route('locale.switch', 'de') }}" class="block px-4 py-2 text-sm {{ app()->getLocale() === 'de' ? 'text-primary-600 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">{{ __('messages.de') }}</a>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700">{{ __('messages.username') }}</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('messages.password') }}</label>
                    <input type="password" name="password" id="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-base px-4 py-3">
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">{{ __('messages.remember_me') }}</label>
                </div>

                <button type="submit"
                        class="w-full py-2 px-4 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    {{ __('messages.login_button') }}
                </button>

                <div class="mt-4 text-center">
                    <a href="{{ route('home') }}" class="text-sm text-primary-600 hover:text-primary-800 hover:underline">
                        {{ __('messages.back_to_homepage') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
