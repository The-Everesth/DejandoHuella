

@extends('layouts.app')

@section('content')
  <div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Mis Citas</h1>

    {{-- Filtros de estado --}}
    <div class="mb-6 flex flex-wrap gap-2">
      @foreach($statusFilters as $key => $label)
        <a href="?status={{ $key }}" class="px-4 py-1 rounded-full border text-sm font-medium
          {{ request('status', 'todas') === $key ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>

    @php
      // Si hay citas en la base, aunque el filtro actual esté vacío, no mostrar el mensaje vacío
      $hasAnyAppointment = isset($allAppointments) ? $allAppointments->count() > 0 : $appointments->count() > 0;
    @endphp
    @if(!$hasAnyAppointment)
      <div class="flex flex-col items-center justify-center py-16 bg-white rounded-lg shadow-md">
        {{-- Icono SVG mascota --}}
        <svg class="w-16 h-16 mb-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <circle cx="7" cy="7" r="3" />
          <circle cx="17" cy="7" r="3" />
          <ellipse cx="12" cy="17" rx="7" ry="4" />
        </svg>
        <p class="text-lg text-gray-600 font-semibold">Aún no tienes citas registradas</p>
        <p class="text-gray-400 mb-4">Solicita tu primera cita para ver el seguimiento aquí.</p>
        <a href="{{ route('services.index') }}" class="mt-2 px-5 py-2 bg-blue-600 text-white rounded-lg font-medium shadow hover:bg-blue-700 transition">Solicitar cita</a>
      </div>
    @elseif($appointments->isEmpty())
      <div class="flex flex-col items-center justify-center py-16 bg-white rounded-lg shadow-md">
        <svg class="w-16 h-16 mb-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <circle cx="7" cy="7" r="3" />
          <circle cx="17" cy="7" r="3" />
          <ellipse cx="12" cy="17" rx="7" ry="4" />
        </svg>
        <p class="text-lg text-gray-500">No hay citas con el estado seleccionado.</p>
      </div>
    @else
      <div class="space-y-6">
        @foreach($appointments as $appointment)
          {{-- Card de cita --}}
          <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col gap-4 border border-gray-100">
            {{-- Encabezado --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div class="flex items-center gap-3">
                {{-- Icono mascota --}}
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <circle cx="7" cy="7" r="3" />
                  <circle cx="17" cy="7" r="3" />
                  <ellipse cx="12" cy="17" rx="7" ry="4" />
                </svg>
                <div>
                  <div class="text-xl font-bold text-gray-800">{{ $appointment->pet_name }}</div>
                  <div class="text-gray-500 text-sm">
                    {{ $appointment->pet_species ?? '-' }} 
                    @if($appointment->pet_breed) · {{ $appointment->pet_breed }} @endif
                    @if($appointment->pet_age) · {{ $appointment->pet_age }} años @endif
                  </div>
                </div>
              </div>
              {{-- Badge de estado --}}
              @php
                $statusMap = [
                  'pendiente' => ['Pendiente', 'bg-yellow-100 text-yellow-800 border-yellow-300'],
                  'pending' => ['Pendiente', 'bg-yellow-100 text-yellow-800 border-yellow-300'],
                  'confirmada' => ['Confirmada', 'bg-green-100 text-green-800 border-green-300'],
                  'confirmed' => ['Confirmada', 'bg-green-100 text-green-800 border-green-300'],
                  'cancelada' => ['Cancelada', 'bg-red-100 text-red-800 border-red-300'],
                  'cancelled' => ['Cancelada', 'bg-red-100 text-red-800 border-red-300'],
                  'finalizada' => ['Finalizada', 'bg-blue-100 text-blue-800 border-blue-300'],
                  'completed' => ['Finalizada', 'bg-blue-100 text-blue-800 border-blue-300'],
                ];
                [$statusLabel, $statusClasses] = $statusMap[$appointment->status] ?? ['Desconocido', 'bg-gray-100 text-gray-500 border-gray-300'];
              @endphp
              <span class="inline-block px-4 py-1 rounded-full border text-sm font-semibold {{ $statusClasses }}">
                {{ $statusLabel }}
              </span>
            </div>

            {{-- Cuerpo --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700">
              <div class="flex items-center gap-2">
                {{-- Icono fecha --}}
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="4" width="18" height="18" rx="4" />
                  <path d="M16 2v4M8 2v4M3 10h18" />
                </svg>
                <span>
                  {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d \\d\\e F \\d\\e Y, g:i A') : '-' }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                {{-- Icono clínica --}}
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="3" y="7" width="18" height="13" rx="2" />
                  <path d="M8 7V5a4 4 0 0 1 8 0v2" />
                </svg>
                <span>{{ $appointment->clinic_name }}</span>
              </div>
              <div class="flex items-center gap-2">
                {{-- Icono servicio --}}
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M12 2v20M2 12h20" />
                </svg>
                <span>{{ $appointment->service_name }}</span>
              </div>
              @if(!empty($appointment->extra_services))
                <div class="flex items-center gap-2 flex-wrap">
                  {{-- Icono extras --}}
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M8 12h8M12 8v8" />
                  </svg>
                  <div class="flex flex-wrap gap-2">
                    @foreach($appointment->extra_services as $extra)
                      <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">{{ friendly_service_name($extra) }}</span>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            {{-- Nota del veterinario --}}
            @if(!empty($appointment->vet_notes))
              <div class="flex items-start gap-2 mt-2">
                {{-- Icono nota --}}
                <svg class="w-5 h-5 text-blue-400 mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <rect x="4" y="4" width="16" height="16" rx="2" />
                  <path d="M8 8h8M8 12h6M8 16h4" />
                </svg>
                <div class="bg-gray-100 rounded-lg px-4 py-3 text-gray-700 w-full">
                  <span class="block text-sm font-medium text-gray-600 mb-1">Nota del veterinario:</span>
                  <span class="text-base">{{ $appointment->vet_notes }}</span>
                </div>
              </div>
            @endif

            {{-- Acciones --}}
            <div class="flex flex-wrap gap-2 mt-4">
              <a href="{{ route('my.appointments.show', $appointment->id ?? $appointment->id ?? '') }}" class="px-4 py-1 rounded-lg bg-blue-50 text-blue-700 font-medium border border-blue-200 hover:bg-blue-100 transition">Ver detalle</a>
              @if($appointment->can_cancel)
                <form method="POST" action="{{ route('my.appointments.cancel', $appointment->id ?? $appointment->id ?? '') }}" onsubmit="return confirm('¿Seguro que deseas cancelar esta cita?');" class="inline">
                  @csrf
                  <button type="submit" class="px-4 py-1 rounded-lg bg-red-50 text-red-700 font-medium border border-red-200 hover:bg-red-100 transition">Cancelar</button>
                </form>
              @endif
              @if($appointment->can_reschedule)
                <a href="{{ route('my.appointments.reschedule.form', $appointment->id ?? $appointment->id ?? '') }}" class="px-4 py-1 rounded-lg bg-yellow-50 text-yellow-700 font-medium border border-yellow-200 hover:bg-yellow-100 transition">Reagendar</a>
              @endif
              {{-- Puedes agregar más acciones aquí --}}
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
@endsection
