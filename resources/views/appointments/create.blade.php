
@extends('layouts.app')

@section('content')

<div class="min-h-[80vh] flex items-center justify-center py-8">
  <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl p-6 sm:p-10 border border-gray-100">
    <h2 class="text-2xl font-bold mb-4 text-center text-teal-700">Agendar cita médica</h2>
    <p class="mb-6 text-center text-gray-600"><span class="font-semibold">Clínica:</span> {{ $clinic['name'] ?? '' }}<span class="mx-2">|</span><span class="font-semibold">Servicio:</span> {{ $service['name'] ?? '' }}</p>


    @if(session('success'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded shadow">{{ session('success') }}</div>
    @elseif($errors->any())
      <div class="mb-4 p-3 bg-red-100 text-red-800 rounded shadow">
        <b>Revisa lo siguiente:</b>
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif


    @if($pets->isEmpty())
      <p class="text-center text-gray-500">No tienes mascotas registradas.</p>
    @else
    <form method="GET" action="" class="mb-6 flex gap-2 items-end justify-center">
      <div class="w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1" for="date">Fecha de la cita</label>
        <input class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition" type="date" name="date" id="date" value="{{ $date }}" min="{{ now()->format('Y-m-d') }}" required placeholder="Selecciona la fecha">
      </div>
    </form>

    <form method="POST" action="{{ route('appointments.store') }}" class="space-y-8">
      @csrf
      <input type="hidden" name="clinic_id" value="{{ $clinic['id'] ?? '' }}">
      <input type="hidden" name="medical_service_id" value="{{ $service['id'] ?? '' }}">

      <!-- Sección: Datos de la cita -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-teal-700 mb-2 flex items-center gap-2">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Datos de la cita
        </h3>
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="start_at" class="block text-sm font-medium text-gray-700 mb-1">Hora disponible</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              @forelse($slots as $slot)
                <label class="cursor-pointer">
                  <input type="radio" name="start_at" value="{{ $date }} {{ $slot }}" class="peer sr-only" required>
                  <div class="w-full rounded-lg border border-gray-300 px-3 py-2 text-center transition peer-checked:bg-teal-600 peer-checked:text-white peer-checked:border-teal-600 bg-gray-50 hover:bg-teal-50">
                    {{ $slot }}
                  </div>
                </label>
              @empty
                <div class="col-span-full text-gray-400 text-center">No hay horarios disponibles</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <!-- Sección: Mascota y contacto -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-teal-700 mb-2 flex items-center gap-2">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Mascota y contacto
        </h3>
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="pet_id" class="block text-sm font-medium text-gray-700 mb-1">Mascota</label>
            <select class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition" name="pet_id" id="pet_id" required>
              <option value="" disabled selected>Selecciona una mascota</option>
              @foreach($pets as $p)
                <option value="{{ $p['id'] ?? '' }}">{{ $p['name'] ?? 'Sin nombre' }} ({{ $p['species'] ?? 'N/A' }})</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="contact" class="block text-sm font-medium text-gray-700 mb-1">Contacto</label>
            <input class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition" type="text" name="contact" id="contact" value="{{ old('contact', auth()->user()->email) }}" required placeholder="Correo o teléfono de contacto">
          </div>
        </div>
      </div>

      <!-- Sección: Servicios adicionales -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-teal-700 mb-2 flex items-center gap-2">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 018 0v2m-4-4a4 4 0 00-4-4V7a4 4 0 018 0v2a4 4 0 00-4 4z"/></svg>
          Servicios adicionales
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          @if(count($clinicServices))
            @foreach($clinicServices as $srv)
              <label class="flex items-center gap-3 cursor-pointer bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 transition hover:border-teal-400 peer-checked:border-teal-600">
                <input type="checkbox" name="extra_services[]" value="{{ $srv['id'] }}" class="accent-teal-600 w-5 h-5">
                <span class="text-gray-700">{{ $srv['name'] }}</span>
              </label>
            @endforeach
          @else
            <div class="col-span-full text-gray-400 text-center">No hay servicios adicionales</div>
          @endif
        </div>
      </div>

      <!-- Sección: Notas -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-teal-700 mb-2 flex items-center gap-2">
          <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Notas
        </h3>
        <textarea class="w-full rounded-lg border border-gray-300 p-2 focus:ring-2 focus:ring-teal-400 focus:border-teal-500 transition" name="notes" id="notes" rows="3" placeholder="¿Algo que debamos saber? (opcional)">{{ old('notes') }}</textarea>
      </div>

      <button type="submit" class="w-full mt-6 bg-teal-600 text-white text-lg font-semibold py-3 rounded-xl shadow hover:bg-teal-700 transition focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2">Solicitar cita</button>
    </form>
    @endif
  </div>
</div>

@endsection
