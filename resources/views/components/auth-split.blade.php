<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased bg-slate-50">
        <div class="min-h-screen flex">
            <!-- Form side -->
            <div class="w-full lg:w-1/2 flex flex-col items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto mb-8 lg:hidden">
                    {{ $slot }}
                </div>
            </div>

            <!-- Brand side -->
            <div class="hidden lg:flex lg:w-1/2 bg-primary relative overflow-hidden items-center justify-center px-12">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute bottom-0 -left-16 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
                </div>

                <div class="relative z-10 max-w-md text-white">
                    <div class="inline-block bg-white rounded-lg px-4 py-2.5 mb-10">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-7 w-auto">
                    </div>

                    <h2 class="font-headline font-bold text-3xl tracking-tight mb-4">
                        {{ $heading ?? 'Healthcare scheduling, made simple.' }}
                    </h2>
                    <p class="text-white/80 text-sm leading-relaxed mb-10">
                        {{ $subheading ?? 'Book appointments with your doctor, track upcoming visits, and manage everything in one place.' }}
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                            </div>
                            <span class="text-sm text-white/90">Browse doctors and open time slots</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                            </div>
                            <span class="text-sm text-white/90">Book in seconds, no phone calls</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                            </div>
                            <span class="text-sm text-white/90">Cancel anytime, slot reopens instantly</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
