<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Servicios Médicos</h2>
    <a class="underline" href="{{ route('admin.services.create') }}">Crear servicio</a>

    <div class="mt-4 space-y-3">
        @foreach($services as $s)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $s->name }}</div>
                <div class="text-sm text-gray-600">{{ $s->description }}</div>

                <div class="mt-2 flex gap-3">
                    <a class="underline" href="{{ route('admin.services.edit', $s) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.services.destroy', $s) }}">
                        @csrf @method('DELETE')
                        <button class="underline">Eliminar</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
