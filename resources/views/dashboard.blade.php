@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            @if($mainRole === 'ciudadano')
                ¡Bienvenido, {{ $user->name }}!
            @elseif($mainRole === 'veterinario')
                Panel del Veterinario
            @elseif($mainRole === 'refugio' || $mainRole === 'institucion')
                Panel de Refugio/Institución
            @else
                Dashboard
            @endif
        </h2>
        <p class="text-gray-500 mt-1">
            @if($mainRole === 'ciudadano')
                Aquí puedes gestionar tus mascotas, citas y adopciones.
            @elseif($mainRole === 'veterinario')
                Gestiona tus clínicas, servicios y agenda de citas.
            @elseif($mainRole === 'refugio' || $mainRole === 'institucion')
                Publica adopciones y gestiona solicitudes.
            @endif
        </p>
    </div>

    {{-- Tarjetas de resumen --}}
    @include('dashboard.partials.stats', ['mainRole' => $mainRole])

    {{-- Acciones rápidas --}}
    <div class="mt-8">
        @include('dashboard.partials.quick-actions', ['mainRole' => $mainRole])
    </div>

    @if($mainRole === 'ciudadano')
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- ...sección original ciudadano... --}}
        </div>
    @elseif($mainRole === 'refugio' || $mainRole === 'institucion')
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Se comento esta parte por que aun no se logra conectar con la funcionalidad
            <div>
                <div class="font-semibold text-gray-600 mb-1">Adopciones publicadas</div>
                <div class="flex flex-row flex-wrap gap-4 pb-2">
                    @forelse($adoptionsPaginator as $adoption)
                        @include('dashboard.partials.mini-adoption-card', ['adoption' => $adoption])
                    @empty
                        <div class="flex flex-col items-center justify-center bg-slate-50 rounded-lg p-6 text-gray-400 min-w-[220px]">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                            <div class="font-semibold">Sin adopciones publicadas</div>
                            <div class="text-xs">Cuando publiques adopciones aparecerán aquí.</div>
                        </div>
                    @endforelse
                </div>
                <div class="mt-2">
                    {{ $adoptionsPaginator->links() }}
                </div>
            </div>
            -->
            <div>
                <div class="font-semibold text-gray-600 mb-1">Solicitudes pendientes</div>
                <div class="flex flex-row gap-4 overflow-x-auto pb-2">
                    @forelse($recentRequests as $req)
                        @include('dashboard.partials.mini-request-card', ['request' => $req])
                    @empty
                        <div class="flex flex-col items-center justify-center bg-slate-50 rounded-lg p-6 text-gray-400 min-w-[220px]">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                            <div class="font-semibold">Sin solicitudes pendientes</div>
                            <div class="text-xs">Cuando recibas solicitudes pendientes aparecerán aquí.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        {{-- Actividad reciente --}}
        <div class="mt-10">
            @include('dashboard.partials.recent-activity', ['mainRole' => $mainRole])
        </div>
    @endif
@endsection
