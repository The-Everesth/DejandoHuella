@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
  {{-- Dashboard Header --}}
  <h1 class="text-3xl font-bold mb-6 text-gray-800">Gestión de Citas</h1>

  {{-- Metric Cards --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <x-metric-card label="Total" :value="$metrics['total'] ?? ($appointments->count() ?? 0)" color="bg-blue-100" icon="calendar-days" />
    <x-metric-card label="Pendientes" :value="$metrics['pending'] ?? 0" color="bg-yellow-100" icon="clock" />
    <x-metric-card label="Confirmadas" :value="$metrics['confirmed'] ?? 0" color="bg-green-100" icon="check-circle" />
    <x-metric-card label="Rechazadas" :value="$metrics['rejected'] ?? 0" color="bg-red-100" icon="x-circle" />
    @if(isset($metrics['cancelled']))
      <x-metric-card label="Canceladas" :value="$metrics['cancelled']" color="bg-gray-100" icon="ban" />
    @endif
  </div>

  {{-- Filter Toolbar --}}
  <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4 mb-6">
    <div class="flex gap-2">
      @php
        $statuses = [
          'all' => 'Todas',
          'PENDING' => 'Pendientes',
          'CONFIRMED' => 'Confirmadas',
          'REJECTED' => 'Rechazadas',
        ];
        if(isset($metrics['cancelled'])) $statuses['CANCELLED'] = 'Canceladas';
      @endphp
      @foreach($statuses as $key => $label)
        <a href="{{ route('vet.appointments.index', array_merge(request()->except('page'), ['status' => $key === 'all' ? null : $key])) }}"
           class="px-3 py-1 rounded-full text-sm font-medium
           {{ (request('status') === $key || ($key === 'all' && !request('status'))) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-blue-100' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>
  </form>

  {{-- Appointment Cards --}}
  @if($appointments->count())
    <div class="grid gap-6">
      @foreach($appointments as $appointment)
        <x-appointment-card :appointment="$appointment" />
      @endforeach
    </div>
    {{-- Paginación si aplica --}}
    @if(method_exists($appointments, 'links'))
    <div class="mt-6">
      {{ $appointments->withQueryString()->links() }}
    </div>
    @endif
  @else
    <div class="flex flex-col items-center justify-center py-16 text-gray-500">
      <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 48 48">
        <circle cx="24" cy="24" r="22" stroke-width="2" />
        <path d="M16 24h16M24 16v16" stroke-width="2" stroke-linecap="round"/>
      </svg>
      <p class="text-lg font-semibold">
        @if(request('status') || request('search'))
          No se encontraron citas con los filtros seleccionados.
        @else
          No hay citas para mostrar.
        @endif
      </p>
    </div>
  @endif
</div>

@push('scripts')
<script>
function toggleNoteForm(id) {
  const el = document.getElementById(id);
  if (el) el.classList.toggle('hidden');
}
</script>
@endpush
@endsection
