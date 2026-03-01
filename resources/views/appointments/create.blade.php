
<x-app-layout>
<h2 class="text-xl font-bold mb-2">Agendar cita</h2>
<p class="mb-4"><b>Clínica:</b> {{ $clinic->name }} — <b>Servicio:</b> {{ $service->name }}</p>

@if(session('success'))
  <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
@elseif($errors->any())
  <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
    <b>Revisa lo siguiente:</b>
    <ul class="list-disc pl-5">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if($pets->isEmpty())
  <p>No tienes mascotas registradas.</p>
@else
<form method="GET" action="" class="mb-4 flex gap-2 items-end">
  <div>
    <label class="block">Fecha</label>
    <input class="border rounded p-2" type="date" name="date" value="{{ $date }}" min="{{ now()->format('Y-m-d') }}" required>
  </div>
  <button class="border rounded px-3 py-2 bg-teal-700 text-white" type="submit">Ver horarios</button>
</form>

<form method="POST" action="{{ route('appointments.store') }}" class="space-y-3">
@csrf
<input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
<input type="hidden" name="medical_service_id" value="{{ $service->id }}">

<div>
  <label class="block">Mascota</label>
  <select class="border rounded p-2 w-full" name="pet_id" required>
    @foreach($pets as $p)
      <option value="{{ $p['id'] ?? '' }}">
        {{ $p['name'] ?? 'Sin nombre' }} ({{ $p['species'] ?? 'N/A' }})
      </option>
    @endforeach
  </select>
</div>

<div>
  <label class="block">Contacto</label>
  <input class="border rounded p-2 w-full" type="text" name="contact" value="{{ old('contact', auth()->user()->email) }}" required>
</div>

<div>
  <label class="block">Servicios extra</label>
  <select class="border rounded p-2 w-full" name="extra_services[]" multiple>
    @foreach($clinicServices as $srv)
      @if(($srv['id'] ?? '') != ($service->id ?? $service['id'] ?? ''))
        <option value="{{ $srv['id'] }}">{{ $srv['name'] }}</option>
      @endif
    @endforeach
  </select>
  <small class="text-xs text-gray-500">Ctrl+Click para seleccionar varios</small>
</div>

<div>
  <label class="block">Hora disponible</label>
  <select class="border rounded p-2 w-full" name="start_at" required>
    @forelse($slots as $slot)
      <option value="{{ $date }} {{ $slot }}">{{ $slot }}</option>
    @empty
      <option value="">No hay horarios disponibles</option>
    @endforelse
  </select>
</div>

<div>
  <label class="block">Notas (opcional)</label>
  <textarea class="border rounded p-2 w-full" name="notes" rows="3">{{ old('notes') }}</textarea>
</div>

<button class="border rounded px-4 py-2">Solicitar cita</button>
</form>
@endif

</x-app-layout>
