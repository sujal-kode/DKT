<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
            <div class="flex items-center gap-2 font-headline font-bold text-lg text-primary tracking-tight mb-6">
                <x-application-logo class="w-6 h-6" />
                <span>{{ config('app.name') }}</span>
            </div>

            <div class="w-full sm:max-w-md bg-white border border-slate-200/80 shadow-xl shadow-slate-200/40 overflow-hidden sm:rounded-xl px-6 py-8 sm:px-10">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
