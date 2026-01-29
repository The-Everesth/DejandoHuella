<x-app-layout>
<h2 class="text-xl font-bold mb-2">Agendar cita</h2>
<p class="mb-4"><b>Clínica:</b> {{ $clinic->name }} — <b>Servicio:</b> {{ $service->name }}</p>

@if($pets->isEmpty())
  <p>No tienes mascotas registradas.</p>
@else
<form method="POST" action="{{ route('appointments.store') }}" class="space-y-3">
@csrf
<input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
<input type="hidden" name="medical_service_id" value="{{ $service->id }}">

<div>
  <label class="block">Mascota</label>
  <select class="border rounded p-2 w-full" name="pet_id" required>
    @foreach($pets as $p)
      <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->species }})</option>
    @endforeach
  </select>
</div>

<div>
  <label class="block">Fecha y hora</label>
  <input class="border rounded p-2 w-full" type="datetime-local" name="scheduled_at" required>
</div>

<div>
  <label class="block">Notas (opcional)</label>
  <textarea class="border rounded p-2 w-full" name="notes" rows="3"></textarea>
</div>

<button class="border rounded px-4 py-2">Solicitar cita</button>
</form>
@endif

</x-app-layout>
