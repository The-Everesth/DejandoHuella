<x-app-layout>
<h2 class="text-xl font-bold mb-2">Clínicas para: {{ $service->name }}</h2>

<div class="space-y-3 mt-4">
@forelse($service->clinics as $c)
  <div class="p-4 border rounded">
    <div class="font-semibold">{{ $c->name }}</div>
    <div class="text-sm text-gray-600">{{ $c->address_line }}, {{ $c->city }}, {{ $c->state }}</div>

    <a class="underline" href="{{ route('appointments.create', [$c, $service]) }}">Agendar cita</a>
  </div>
@empty
  <p>No hay clínicas registradas para este servicio.</p>
@endforelse
</div>

</x-app-layout>
