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
        <main class="flex-1 w-full bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 pt-24">
                <x-flash />
                @yield('content')
            </div>
        </main>

        <!-- Footer always at bottom -->
        <footer class="bg-teal-700 text-white mt-20">
            <div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    
                    {{-- Acerca de --}}
                    <div>
                        <h3 class="text-lg font-bold mb-3">Dejando Huella</h3>
                        <p class="text-teal-100 text-sm">
                            Conectando mascotas con hogares. Una plataforma para adoptar, cuidar y conectar.
                        </p>
                    </div>

                    {{-- Links --}}
                    <div>
                        <h4 class="font-bold mb-3">Enlaces</h4>
                        <ul class="space-y-2 text-sm text-teal-100">
                            <li><a href="/" class="hover:text-white transition">Inicio</a></li>
                            <li><a href="{{ route('adopciones.form') }}" class="hover:text-white transition">Adopciones</a></li>
                            <li><a href="{{ route('services.index') }}" class="hover:text-white transition">Servicios Médicos</a></li>
                        </ul>
                    </div>

                    {{-- Contacto --}}
                    <div>
                        <h4 class="font-bold mb-3">Contacto</h4>
                        <p class="text-teal-100 text-sm">
                            ¿Tienes dudas? <br>
                            <a href="{{ auth()->check() ? route('tickets.index') : route('login') }}" class="text-white hover:underline font-semibold">
                                Reportar problema
                            </a>
                        </p>
                    </div>

                </div>

                <div class="border-t border-teal-600 pt-6 text-center text-sm text-teal-100">
                    <p>&copy; {{ date('Y') }} Dejando Huella. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
@stack('scripts')
</html>
