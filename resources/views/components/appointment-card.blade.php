@props(['appointment'])

@php
    $statusColors = [
        'PENDING' => 'bg-yellow-100 text-yellow-800',
        'CONFIRMED' => 'bg-green-100 text-green-800',
        'REJECTED' => 'bg-red-100 text-red-800',
        'CANCELLED' => 'bg-gray-100 text-gray-600',
    ];
    $statusLabels = [
        'PENDING' => 'Pendiente',
        'CONFIRMED' => 'Confirmada',
        'REJECTED' => 'Rechazada',
        'CANCELLED' => 'Cancelada',
    ];
    $status = $appointment->status;
@endphp

<div class="bg-white rounded-xl shadow p-6 flex flex-col md:flex-row md:items-center gap-4">
    {{-- Header --}}
    <div class="flex-1">
        <div class="flex items-center justify-between mb-2">
            <div>
                <div class="text-xl font-bold text-gray-800">{{ $appointment->petName ?? 'Mascota' }}</div>
                <div class="text-sm text-gray-500">{{ $appointment->serviceName ?? $appointment->serviceId ?? 'Servicio' }}</div>
            </div>
            <span class="ml-2 px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $statusLabels[$status] ?? ucfirst(strtolower($status)) }}
            </span>
        </div>
        <div class="text-lg font-semibold text-blue-700 flex items-center gap-2 mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ \Carbon\Carbon::parse($appointment->startAt)->format('d M Y, H:i') }}
        </div>
        {{-- Body --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
            <div><span class="font-medium">Clínica:</span> {{ $appointment->clinicName ?? $appointment->clinicId ?? 'Clínica' }}</div>
            <div><span class="font-medium">Cliente:</span> {{ $appointment->userName ?? $appointment->userUid ?? 'Cliente' }}</div>
            <div><span class="font-medium">Contacto:</span> {{ $appointment->contact }}</div>
            @if(!empty($appointment->notes))
                <div class="md:col-span-2"><span class="font-medium">Nota del cliente:</span> {{ $appointment->notes }}</div>
            @endif
            @if(!empty($appointment->vetNotes))
                <div class="md:col-span-2"><span class="font-medium">Nota vet:</span> {{ $appointment->vetNotes }}</div>
            @endif
        </div>
    </div>
    {{-- Actions --}}
    <div class="flex flex-col gap-2 md:w-48">
        @if($status === 'PENDING')
            <form method="POST" action="{{ route('vet.appointments.status', $appointment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="CONFIRMED">
                <button type="submit" class="w-full bg-green-600 text-white py-1 rounded hover:bg-green-700 transition">Confirmar</button>
            </form>
            <form method="POST" action="{{ route('vet.appointments.status', $appointment->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="REJECTED">
                <button type="submit" class="w-full bg-red-600 text-white py-1 rounded hover:bg-red-700 transition">Rechazar</button>
            </form>
        @endif
        <button type="button"
            class="w-full bg-blue-100 text-blue-700 py-1 rounded hover:bg-blue-200 transition"
            onclick="toggleNoteForm('note-form-{{ $appointment->id }}')">
            {{ !empty($appointment->vetNotes) ? 'Editar nota' : 'Agregar nota' }}
        </button>
                <form id="note-form-{{ $appointment->id }}" method="POST"
                            action="{{ route('vet.appointments.note', $appointment->id) }}"
                            class="hidden mt-2">
                        @csrf @method('PATCH')
                        <textarea name="vetNotes" rows="2" class="w-full border rounded p-2 text-sm mb-2" placeholder="Escribe una nota para el usuario...">{{ old('vetNotes', $appointment->vetNotes ?? '') }}</textarea>
                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">Guardar nota</button>
                </form>
    </div>
</div>
