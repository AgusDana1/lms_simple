<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'LMS - Learning Management System' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">
    <!-- Ambient glowing backdrop -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-200/60 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-40 w-96 h-96 bg-violet-200/50 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 right-1/3 w-96 h-96 bg-sky-200/50 rounded-full blur-3xl"></div>
    </div>

    <div class="min-h-full flex flex-col justify-center px-4 py-8 sm:py-12 sm:px-6 lg:px-8">
        {{ $slot }}
    </div>
</body>
</html>
