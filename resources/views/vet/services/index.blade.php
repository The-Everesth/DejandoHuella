@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Servicios globales</h1>
        <a href="{{ route('vet.services.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded shadow mb-6 inline-block">Nuevo servicio</a>
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        <div class="space-y-4">
            @forelse($services as $service)
                <div class="bg-gray-50 rounded p-4 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="font-semibold text-lg text-gray-900">{{ $service['name'] ?? '' }}</div>
                        <div class="text-gray-600 text-sm mb-1">{{ $service['description'] ?? '' }}</div>
                        <div class="flex gap-2 text-xs text-gray-700">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">${{ number_format($service['base_price'] ?? 0, 2) }} MXN</span>
                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">{{ $service['duration_minutes'] ?? '' }} min</span>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3 md:mt-0">
                        <a href="{{ route('vet.services.edit', $service['id']) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">Editar</a>
                        <form action="{{ route('vet.services.destroy', $service['id']) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-gray-500">No tienes servicios registrados.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
