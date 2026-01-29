<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Crear publicación</h2>

    @if($pets->isEmpty())
        <p>No tienes mascotas disponibles (o ya están publicadas).</p>
    @else
        <form method="POST" action="{{ route('myadoptions.store') }}" class="space-y-3">
            @csrf

            <div>
                <label class="block">Mascota</label>
                <select name="pet_id" class="border rounded p-2 w-full" required>
                    @foreach($pets as $pet)
                        <option value="{{ $pet->id }}">{{ $pet->name }} ({{ $pet->species }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block">Título</label>
                <input name="title" class="border rounded p-2 w-full" required>
            </div>

            <div>
                <label class="block">Descripción</label>
                <textarea name="description" class="border rounded p-2 w-full" rows="4" required></textarea>
            </div>

            <div>
                <label class="block">Requisitos (opcional)</label>
                <textarea name="requirements" class="border rounded p-2 w-full" rows="3"></textarea>
            </div>

            <button class="px-4 py-2 border rounded">Publicar</button>
        </form>
    @endif
</x-app-layout>
