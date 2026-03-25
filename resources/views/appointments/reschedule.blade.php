@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Reagendar Cita</h1>
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
        <form method="POST" action="{{ route('my.appointments.reschedule', $appointment['id'] ?? $appointment['id'] ?? '') }}">
            @csrf
            <div class="mb-4">
                <label class="block font-semibold mb-1">Nueva fecha y hora</label>
                <input type="datetime-local" name="new_date" class="border rounded px-3 py-2 w-full" required>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar cambios</button>
            <a href="{{ route('my.appointments') }}" class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Cancelar</a>
        </form>
    </div>
</div>
@endsection
