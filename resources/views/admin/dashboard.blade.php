<x-app-layout>
    <x-page-title 
        title="Panel de Administración"
        subtitle="Vista general del sistema DejandoHuella"
    />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <x-card>
            <div class="text-sm text-gray-500 font-semibold">Usuarios</div>
            <div class="text-3xl font-extrabold text-gray-900 mt-2">
                {{ $usersCount ?? '—' }}
            </div>
        </x-card>

        <x-card>
            <div class="text-sm text-gray-500 font-semibold">Solicitudes de rol pendientes</div>
            <div class="text-3xl font-extrabold text-yellow-600 mt-2">
                {{ $pendingUsers ?? '—' }}
            </div>
        </x-card>

        <x-card>
            <div class="text-sm text-gray-500 font-semibold">Tickets abiertos</div>
            <div class="text-3xl font-extrabold text-red-600 mt-2">
                {{ $openTickets ?? '—' }}
            </div>
        </x-card>

    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <x-card>
            <div class="font-bold text-gray-900">Usuarios</div>
            <p class="text-gray-600 text-sm mt-1">
                Administrar cuentas y roles.
            </p>
            <div class="mt-4">
                <a href="{{ route('admin.users.index') }}">
                    <x-button variant="outline">Abrir</x-button>
                </a>
            </div>
        </x-card>

        <x-card>
            <div class="font-bold text-gray-900">Tickets</div>
            <p class="text-gray-600 text-sm mt-1">
                Mensajes enviados por usuarios.
            </p>
            <div class="mt-4">
                <a href="{{ route('admin.tickets.index') }}">
                    <x-button variant="outline">Abrir</x-button>
                </a>
            </div>
        </x-card>

        <x-card>
            <div class="font-bold text-gray-900">Gestión de adopciones</div>
            <p class="text-gray-600 text-sm mt-1">Modera publicaciones creadas por refugios y veterinarias.</p>
            <div class="mt-3 flex flex-wrap gap-2 text-sm font-semibold">
                <span class="rounded-full bg-teal-50 px-3 py-1 text-teal-700">
                    Visibles: {{ $visibleAdoptionsCount ?? '—' }}
                </span>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                    Ocultas: {{ $hiddenAdoptionsCount ?? '—' }}
                </span>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.adoptions.index') }}"><x-button variant="outline">Abrir</x-button></a>
            </div>
        </x-card>

    </div>
</x-app-layout>
