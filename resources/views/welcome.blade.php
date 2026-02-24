<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-900">Bienvenido a Dejando Huella</h1>
        <p class="text-gray-600 mt-2">Portal de adopción y servicios para el cuidado animal.</p>
    </x-slot>

    <div class="bg-white rounded-xl shadow p-8">
        <h2 class="text-2xl font-bold text-gray-900">Comienza aquí</h2>
        <p class="mt-3 text-gray-600">Explora adopciones disponibles, consulta servicios médicos y gestiona tus solicitudes desde tu cuenta.</p>

        <div class="mt-6 flex flex-wrap gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-[#F5E7DA] text-black font-bold px-6 py-2 rounded-full hover:opacity-90 transition">
                    Ir al dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="bg-[#F5E7DA] text-black font-bold px-6 py-2 rounded-full hover:opacity-90 transition">
                    Iniciar sesión
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="border border-slate-300 text-slate-800 font-semibold px-6 py-2 rounded-full hover:bg-slate-50 transition">
                        Crear cuenta
                    </a>
                @endif
            @endauth
        </div>
    </div>
</x-app-layout>
