<header class="h-16 bg-white border-b flex items-center justify-between px-6">
    <button @click="window.innerWidth >= 1024 ? (sidebarOpen = !sidebarOpen) : (mobileSidebarOpen = !mobileSidebarOpen)" class="text-gray-500 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="flex items-center gap-4">
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

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                <span>{{ auth()->user()->name }}</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border z-50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
