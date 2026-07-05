<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', __('messages.course_cms')) }} - @yield('title', __('messages.dashboard_title'))</title>
    <style>
        [x-cloak] { display: none !important; }
        [data-sidebar-desktop] {
            width: 16rem;
            background-color: rgb(17 24 39);
            color: rgb(255 255 255);
            flex-shrink: 0;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            [data-sidebar-desktop] {
                display: flex !important;
                flex-direction: column !important;
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    @yield('content')

    <x-toast />
    @stack('scripts')
</body>
</html>
