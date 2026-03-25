@php
$role = $mainRole;
@endphp
<div class="flex flex-wrap gap-4">
    @if($role === 'ciudadano')
        <a href="{{ route('my.pets.create') }}" class="bg-teal-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-teal-700 transition">Registrar mascota</a>
        <a href="{{ route('my.appointments') }}" class="bg-sky-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-sky-700 transition">Ver mis citas</a>
        <a href="{{ route('my.requests') }}" class="bg-orange-500 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-orange-600 transition">Ver adopciones</a>
    @elseif($role === 'veterinario')
        <a href="{{ route('vet.clinics.index') }}" class="bg-teal-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-teal-700 transition">Mis clínicas</a>
        <a href="{{ route('services.index') }}" class="bg-sky-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-sky-700 transition">Servicios médicos</a>
        <a href="{{ route('vet.appointments.index') }}" class="bg-orange-500 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-orange-600 transition">Ver agenda</a>
        <a href="{{ route('vet.my.adoptions') }}" class="bg-indigo-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-indigo-700 transition">Publicar adopción</a>
        <a href="{{ route('my.published.requests') }}" class="bg-fuchsia-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-fuchsia-700 transition">Ver solicitudes de adopción</a>
    @elseif($role === 'refugio' || $role === 'institucion')
        <a href="{{ route('vet.my.adoptions') }}" class="bg-sky-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-sky-700 transition">Mis publicaciones</a>
        <a href="{{ route('my.published.requests') }}" class="bg-orange-500 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-orange-600 transition">Revisar solicitudes</a>
        <!--<a href="#" onclick="window.dispatchEvent(new CustomEvent('open-adoption-modal'))" class="bg-teal-600 text-white px-5 py-3 rounded-lg font-semibold shadow hover:bg-teal-700 transition">Publicar adopción</a>-->
    @endif
</div>