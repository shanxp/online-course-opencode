@extends('layouts.app')

@section('content')
    <div x-data="{ sidebarOpen: true, mobileSidebarOpen: false }" class="min-h-screen flex">
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            <x-top-nav />

            <main class="flex-1 p-6">
                <x-breadcrumbs />
                <div class="mt-4">
                    @yield('page-content')
                </div>
            </main>
        </div>
    </div>
    <x-confirm-dialog />
@endsection
