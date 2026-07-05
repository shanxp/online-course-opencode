<aside data-sidebar-desktop class="hidden lg:flex lg:flex-col bg-gray-900 text-white shrink-0 transition-all duration-200 overflow-hidden w-80"
       :class="sidebarOpen ? 'w-80' : 'w-16'">
    <div class="flex items-center h-16 px-4 border-b border-gray-700 shrink-0 overflow-hidden bg-white">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
        </a>
    </div>

    <nav class="mt-4 px-3 space-y-1">
        @include('components.sidebar-nav-items')
    </nav>
</aside>

<aside x-show="mobileSidebarOpen" x-cloak data-mobile-sidebar
       class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-white lg:hidden"
       x-transition:enter="transform transition-transform duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transform transition-transform duration-200"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       @click.away="mobileSidebarOpen = false">
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700 bg-white">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto">
        </a>
        <button @click="mobileSidebarOpen = false" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="mt-4 px-3 space-y-1">
        @include('components.sidebar-nav-items')
    </nav>
</aside>

<div x-show="mobileSidebarOpen" x-cloak data-mobile-backdrop
     class="fixed inset-0 z-30 bg-black/50 lg:hidden"
     @click="mobileSidebarOpen = false">
</div>
