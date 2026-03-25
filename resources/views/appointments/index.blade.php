{{-- resources/views/appointments/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Mis Citas</h1>

    @if($appointments->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 bg-white rounded-lg shadow-sm">
            <span class="text-5xl mb-4">🐾</span>
            <p class="text-lg text-gray-500">Aún no tienes citas registradas 🐾</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($appointments as $appointment)
                {{-- Card de cita --}}
                <div class="bg-white rounded-xl shadow-md p-6 flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-block w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center overflow-hidden border border-teal-200">
                                @if(!empty($appointment->pet_photo_url))
                                    <img src="{{ $appointment->pet_photo_url }}" alt="Foto de {{ $appointment->pet_name }}" class="w-12 h-12 object-cover rounded-full">
                                @else
                                    <svg class="w-7 h-7 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                                @endif
                            </span>
                            <h2 class="text-xl font-semibold text-gray-800">{{ $appointment->pet_name }}</h2>
                        </div>
                        {{-- Badge de estado --}}
                        @php
                            $statusMap = [
                                'confirmed' => ['Confirmada', 'bg-green-100 text-green-700'],
                                'pending' => ['Pendiente', 'bg-yellow-100 text-yellow-700'],
                                'rejected' => ['Rechazada', 'bg-red-100 text-red-700'],
                                'cancelled' => ['Cancelada', 'bg-gray-200 text-gray-600'],
                            ];
                            [$statusLabel, $statusClasses] = $statusMap[$appointment->status] ?? ['Desconocido', 'bg-gray-100 text-gray-500'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-4 text-gray-600">
                        <div class="flex items-center gap-2">
                            <span>📅</span>
                            <span>
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y · g:i A') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span>🏥</span>
                            <span>{{ $appointment->clinic_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Servicio:</span>
                            <span>{{ friendly_service_name($appointment->service_name) }}</span>
                        </div>
                    </div>

                    @if(!empty($appointment->extra_services))
                        <div class="flex items-center gap-2">
                            <span class="font-medium">Extras:</span>
                            <ul class="flex flex-wrap gap-2">
                                @foreach($appointment->extra_services as $extra)
                                    <li class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">{{ friendly_service_name($extra) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($appointment->vet_notes))
                        <div class="flex items-start gap-2 mt-2">
                            <span>💬</span>
                            <div class="bg-gray-100 rounded-lg px-4 py-3 text-gray-700 w-full">
                                <span class="block text-sm font-medium text-gray-600 mb-1">Nota del veterinario:</span>
                                <span class="text-base">{{ $appointment->vet_notes }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
