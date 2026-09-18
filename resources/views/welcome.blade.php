<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col">
            <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
                <div class="flex items-center gap-2 font-headline font-bold text-lg text-primary tracking-tight">
                    <x-application-logo class="w-6 h-6" />
                    <span>{{ config('app.name') }}</span>
                </div>

                <nav class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-primary-hover transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:text-primary transition-colors">
                            Log in
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-primary-hover transition-colors">
                            Register
                        </a>
                    @endauth
                </nav>
            </header>

            <main class="flex-grow flex items-center justify-center px-4 py-16">
                <div class="max-w-xl text-center">
                    <div class="w-16 h-16 rounded-full bg-primary-surface flex items-center justify-center text-primary mb-6 ring-4 ring-teal-50 mx-auto">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 15l-4-4 1.41-1.41L11 13.17l5.59-5.59L18 9l-7 7z"/></svg>
                    </div>
                    <h1 class="font-headline font-bold text-3xl sm:text-4xl tracking-tight text-slate-900 mb-4">
                        Book appointments with your doctor, simply.
                    </h1>
                    <p class="text-slate-500 mb-8">
                        Browse available doctors, pick an open time slot, and manage your appointments — all in one place.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-3 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-primary-hover shadow-md shadow-teal-600/20 transition-all">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="px-6 py-3 rounded-lg text-sm font-semibold text-white bg-primary hover:bg-primary-hover shadow-md shadow-teal-600/20 transition-all">
                                Get Started
                            </a>
                            <a href="{{ route('login') }}" class="px-6 py-3 rounded-lg text-sm font-semibold text-slate-700 border border-slate-300 hover:bg-slate-100 transition-colors">
                                Log In
                            </a>
                        @endauth
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
