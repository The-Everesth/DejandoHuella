@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Detalle de Cita</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <div class="mb-4">
            <span class="font-semibold">Mascota:</span>
            @if(isset($pet) && $pet)
                {{ $pet['name'] ?? '-' }} ({{ $pet['species'] ?? '' }})
            @else
                -
            @endif
        </div>
        <div class="mb-4">
            <span class="font-semibold">Fecha y hora:</span> {{ $appointment['startAt'] ?? $appointment['appointment_date'] ?? '-' }}
        </div>
        <div class="mb-4">
            <span class="font-semibold">Clínica:</span>
            @if(isset($clinic) && $clinic)
                {{ $clinic['name'] ?? '-' }}
            @else
                -
            @endif
        </div>
        <div class="mb-4">
            <span class="font-semibold">Servicio:</span>
            @if(isset($service) && $service)
                {{ $service['name'] ?? '-' }}
            @elseif(isset($appointment['medicalServiceId']))
                {{ $appointment['medicalServiceId'] }}
            @else
                -
            @endif
        </div>
        <div class="mb-4">
            <span class="font-semibold">Estado:</span> {{ $appointment['status'] ?? '-' }}
        </div>
        @if((isset($appointment['notes']) && !empty($appointment['notes'])))
        <div class="mb-4">
            <span class="font-semibold">Nota del dueño:</span> {{ $appointment['notes'] }}
        </div>
        @endif

        @if((isset($appointment['vetNotes']) && !empty($appointment['vetNotes'])) || (isset($appointment['vet_notes']) && !empty($appointment['vet_notes'])))
        <div class="mb-4">
            <span class="font-semibold">Nota del veterinario:</span> {{ isset($appointment['vetNotes']) ? $appointment['vetNotes'] : (isset($appointment['vet_notes']) ? $appointment['vet_notes'] : '') }}
        </div>
        @endif
        <a href="{{ route('my.appointments') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Volver a mis citas</a>
    </div>
</div>
@endsection
