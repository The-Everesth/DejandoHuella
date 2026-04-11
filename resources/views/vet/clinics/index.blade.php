@extends('layouts.app')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Mis Clínicas</h2>
        <a href="{{ route('vet.clinics.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 transition">Registrar clínica</a>
    </div>

    @if($clinics->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($clinics as $c)
                <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $c['name'] ?? 'Sin nombre' }}</h3>
                        @if(!empty($c['address']))
                            <p class="text-gray-600 text-sm mt-1">
                                {{ $c['address'] }}
                            </p>
                        @endif
                        @if(!empty($c['phone']))
                            <p class="text-gray-600 text-sm mt-1">Tel: {{ $c['phone'] }}</p>
                        @endif
                        <p class="text-sm mt-2">
                            @if(!empty($c['is_public']))
                                <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Activa</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">Privada</span>
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('vet.clinics.edit', $c['id'] ?? $loop->index) }}" class="flex-1 bg-teal-500 text-white text-center px-3 py-2 rounded hover:bg-teal-600 transition">Ver / Editar</a>
                        <a href="{{ route('vet.clinics.assign_services.edit', $c['id'] ?? $loop->index) }}" class="flex-1 bg-blue-500 text-white text-center px-3 py-2 rounded hover:bg-blue-600 transition">Servicios</a>
                        <form method="POST" action="{{ route('vet.clinics.destroy', $c['id'] ?? $loop->index) }}" onsubmit="return confirm('¿Seguro que deseas eliminar esta clínica?');" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded hover:bg-red-700 transition">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $clinics->links() }}
        </div>
    @else
        <p class="text-gray-500">Aún no registras clínicas.</p>
    @endif
@endsection
