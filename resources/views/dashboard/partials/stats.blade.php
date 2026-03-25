@php
$role = $mainRole;
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @if($role === 'ciudadano')
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Mascotas registradas</div>
            <div class="text-3xl font-bold mt-2">{{ $petsCount ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Citas pendientes</div>
            <div class="text-3xl font-bold mt-2">{{ $pendingAppointmentsCount ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Solicitudes de adopción pendientes</div>
            <div class="text-3xl font-bold mt-2">{{ $pendingAdoptionRequestsCount ?? 0 }}</div>
        </div>
    @elseif($role === 'veterinario')
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Clínicas asociadas</div>
            <div class="text-3xl font-bold mt-2">{{ $clinicsCount ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ ($clinicsCount ?? 0) === 1 ? '1 clínica' : (($clinicsCount ?? 0) > 1 ? ($clinicsCount ?? 0).' clínicas' : 'Sin clínicas') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Servicios médicos</div>
            <div class="text-3xl font-bold mt-2">{{ $servicesCount ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ ($servicesCount ?? 0) === 1 ? '1 servicio' : (($servicesCount ?? 0) > 1 ? ($servicesCount ?? 0).' servicios' : 'Sin servicios') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Adopciones publicadas</div>
            <div class="text-3xl font-bold mt-2">{{ $adoptionsCount ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ ($adoptionsCount ?? 0) === 1 ? '1 adopción' : (($adoptionsCount ?? 0) > 1 ? ($adoptionsCount ?? 0).' adopciones' : 'Sin adopciones') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Citas pendientes</div>
            <div class="text-3xl font-bold mt-2">{{ $recentAppointments->count() ?? 0 }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $recentAppointments->count() > 0 ? $recentAppointments->count().' pendientes' : 'Sin citas pendientes' }}</div>
        </div>
    @elseif($role === 'refugio' || $role === 'institucion')
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Publicaciones de adopción</div>
            <div class="text-3xl font-bold mt-2">{{ $adoptionPostsCount ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <div class="text-gray-500 font-semibold">Solicitudes pendientes</div>
            <div class="text-3xl font-bold mt-2">{{ $pendingRequestsCount ?? 0 }}</div>
        </div>
    @endif
</div>