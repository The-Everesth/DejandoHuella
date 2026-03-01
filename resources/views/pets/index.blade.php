<x-app-layout>
    <x-page-title title="Mis mascotas" />
    <div class="mb-4 flex justify-end">
        <a href="{{ route('my.pets.create') }}" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 transition">Agregar mascota</a>
    </div>
    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-100 rounded p-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 text-red-700 bg-red-100 rounded p-2">{{ session('error') }}</div>
    @endif
    @if(count($pets))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($pets as $pet)
                <div class="bg-white rounded shadow p-4 flex flex-col gap-2">
                    <div class="font-bold text-lg">{{ $pet['name'] }}</div>
                    <div class="text-sm text-gray-600">{{ ucfirst($pet['species']) }} | {{ ucfirst($pet['sex']) }}</div>
                    @if(!empty($pet['breed']))<div class="text-xs text-gray-500">Raza: {{ $pet['breed'] }}</div>@endif
                    @if(!empty($pet['ageYears']))<div class="text-xs text-gray-500">Edad: {{ $pet['ageYears'] }} años</div>@endif
                    @if(!empty($pet['notes']))<div class="text-xs text-gray-500">Notas: {{ $pet['notes'] }}</div>@endif
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('my.pets.edit', $pet['id']) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form method="POST" action="{{ route('my.pets.destroy', $pet['id']) }}" onsubmit="return confirm('¿Eliminar mascota?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-gray-500">No tienes mascotas registradas.</div>
    @endif
</x-app-layout>
