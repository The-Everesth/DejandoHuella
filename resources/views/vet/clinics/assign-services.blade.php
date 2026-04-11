@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Servicios ofrecidos en {{ $clinic['name'] ?? '' }}</h1>
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        <form action="{{ route('vet.clinics.assign_services.update', $clinic['id']) }}" method="POST">
            @csrf
            <div class="space-y-3">
                @forelse($services as $service)
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="services[]" value="{{ $service['id'] }}" id="service_{{ $service['id'] }}" @checked(in_array($service['id'], $selected))>
                        <label for="service_{{ $service['id'] }}" class="font-semibold text-gray-800">{{ $service['name'] ?? '' }}</label>
                        <span class="text-xs text-gray-500">({{ $service['duration_minutes'] ?? ($service['durationMinutes'] ?? '') }} min, ${{ number_format($service['base_price'] ?? ($service['basePrice'] ?? 0), 2) }})</span>
                    </div>
                @empty
                    <div class="text-gray-500">No tienes servicios globales registrados. <a href="{{ route('vet.services.create') }}" class="underline text-blue-600">Crear uno nuevo</a></div>
                @endforelse
            </div>
            <div class="flex justify-end mt-6">
                <a href="{{ route('vet.clinics.index') }}" class="mr-4 text-gray-600 hover:underline">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded shadow">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
