<x-app-layout>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Mis Clínicas</h2>
        <a class="underline" href="{{ route('vet.clinics.create') }}">Registrar clínica</a>
    </div>

    <div class="space-y-3">
        @forelse($clinics as $c)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $c->name }}</div>
                <div class="text-sm text-gray-600">{{ $c->address_line }}, {{ $c->city }}, {{ $c->state }}</div>

                <div class="mt-2 flex gap-3">
                    <a class="underline" href="{{ route('vet.clinics.edit', $c) }}">Editar</a>
                    <a class="underline" href="{{ route('vet.clinics.services.edit', $c) }}">Servicios que ofrece</a>
                    <form method="POST" action="{{ route('vet.clinics.destroy', $c) }}">
                        @csrf @method('DELETE')
                        <button class="underline">Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Aún no registras clínicas.</p>
        @endforelse
    </div>
</x-app-layout>
