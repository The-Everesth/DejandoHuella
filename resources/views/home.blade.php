<x-app-layout>
    {{-- HERO --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
        <x-card class="rounded-3xl">
            <div class="text-sm font-bold text-teal-700">DejandoHuella</div>
            <h1 class="text-3xl md:text-5xl font-extrabold text-gray-900 mt-2">
                Bienestar animal, adopciones y servicios médicos.
            </h1>
            <p class="text-gray-600 mt-4 text-lg">
                Un sistema para conectar dueños, refugios y veterinarios. Todo en un solo lugar.
            </p>

            <div class="mt-6 flex gap-3 flex-wrap">
                @guest
                    <a href="{{ route('login') }}">
                        <x-button variant="primary">Iniciar sesión</x-button>
                    </a>
                    <a href="{{ route('register') }}">
                        <x-button variant="outline">Crear cuenta</x-button>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}">
                        <x-button variant="primary">Ir a mi panel</x-button>
                    </a>
                @endguest
            </div>
        </x-card>

        <x-card class="rounded-3xl">
            <div class="grid grid-cols-2 gap-3">
                <div class="p-4 rounded-2xl bg-teal-50 border">
                    <div class="font-extrabold text-gray-900">Adopciones</div>
                    <div class="text-gray-600 mt-1 text-sm">Publica, solicita y administra.</div>
                </div>
                <div class="p-4 rounded-2xl bg-teal-50 border">
                    <div class="font-extrabold text-gray-900">Servicios Médicos</div>
                    <div class="text-gray-600 mt-1 text-sm">Clínicas, citas y atención.</div>
                </div>
                <div class="p-4 rounded-2xl bg-teal-50 border">
                    <div class="font-extrabold text-gray-900">Refugios</div>
                    <div class="text-gray-600 mt-1 text-sm">Gestión de mascotas y adopción.</div>
                </div>
                <div class="p-4 rounded-2xl bg-teal-50 border">
                    <div class="font-extrabold text-gray-900">Soporte</div>
                    <div class="text-gray-600 mt-1 text-sm">Mensajes a administración.</div>
                </div>
            </div>
        </x-card>
    </div>

    {{-- SECCIÓN INFO --}}
    <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card>
            <div class="font-extrabold text-gray-900">Roles claros</div>
            <div class="text-gray-600 mt-2">Usuario, Veterinario, Refugio y Admin con pantallas dedicadas.</div>
        </x-card>
        <x-card>
            <div class="font-extrabold text-gray-900">Flujo real</div>
            <div class="text-gray-600 mt-2">Aprobaciones, solicitudes y estados visibles para la entrega.</div>
        </x-card>
        <x-card>
            <div class="font-extrabold text-gray-900">Diseño consistente</div>
            <div class="text-gray-600 mt-2">Componentes reutilizables: cards, botones, badges, alerts.</div>
        </x-card>
    </div>
</x-app-layout>
