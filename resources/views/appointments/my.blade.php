<x-app-layout>
<h2 class="text-xl font-bold mb-4">Mis citas</h2>

<div class="space-y-3">
@forelse($appointments as $a)
  <div class="p-4 border rounded">
    <div class="font-semibold">Servicio: {{ $a->serviceName ?? $a->serviceId ?? '-' }} — Mascota: {{ $a->petName ?? $a->petId ?? '-' }}</div>
    <div class="text-sm text-gray-600">Clínica: {{ $a->clinicId ?? '-' }} | {{ $a->startAt ?? '-' }}</div>
    <div>Estado: <b>{{ $a->status }}</b></div>
    @if(!empty($a->vetNotes))
      <div class="text-xs text-green-700">Notas del veterinario: {{ $a->vetNotes }}</div>
    @endif
    @if(!empty($a->extraServicesJson))
      <div class="text-xs text-gray-500">Servicios extra: {{ implode(', ', json_decode($a->extraServicesJson, true) ?? []) }}</div>
    @endif
  </div>
@empty
  <p>No tienes citas.</p>
@endforelse
</div>

</x-app-layout>
