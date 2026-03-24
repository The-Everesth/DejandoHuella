<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-900">

    <!-- Layout wrapper: full height + column -->
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <!-- Page Heading eliminado para evitar franja blanca -->

        <!-- Page Content (grows to push footer down) -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <x-flash />
                @yield('content')
            </div>
        </main>

        <!-- Footer always at bottom -->
        <footer class="bg-teal-700 text-black">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">

                <div>
                    <h3 class="text-2xl font-bold mb-4">Información Animal</h3>
                    <ul class="space-y-3 text-white/90 font-semibold">
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Alimentación Animal
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Enfermedades comunes
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Esterilización/Vacunación
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-2xl font-bold mb-4">Información de contacto</h3>
                    <ul class="space-y-3 text-white/90 font-semibold">
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            Blvd de la Juventud 1006A, Solares 20 de Noviembre, 34288 Durango, Dgo.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-2 h-1.5 w-1.5 rounded-full bg-white"></span>
                            +52 618 - 137 - 8344
                        </li>
                    </ul>
                </div>

                <div class="md:text-right">
                    <h3 class="text-2xl font-bold mb-4">Redes Sociales</h3>
                    <div class="flex md:justify-end gap-6">
                        <a href="#" class="h-14 w-14 rounded-full bg-[#243B6B] flex items-center justify-center hover:opacity-90 transition" aria-label="Facebook">
                            <span class="text-2xl font-black">f</span>
                        </a>
                        <a href="#" class="h-14 w-14 rounded-full bg-[#243B6B] flex items-center justify-center hover:opacity-90 transition" aria-label="Email">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>

            <div class="border-t border-white/20">
                <div class="max-w-7xl mx-auto px-6 lg:px-8 py-4 text-sm text-white/90 flex flex-col md:flex-row gap-2 md:items-center md:justify-between">
                    
                </div>
            </div>
        </footer>
    </div>
</body>
@stack('scripts')
</html>
