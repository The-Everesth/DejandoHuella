<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold">Mis Publicaciones</h2>
        <a class="underline" href="{{ route('myadoptions.create') }}">Crear publicación</a>
    </div>

    <div class="space-y-3">
        @forelse($posts as $post)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $post->title }}</div>
                <div class="text-sm text-gray-600">Mascota: {{ $post->pet->name }}</div>
                <div class="text-sm">Estado: {{ $post->is_active ? 'Activa' : 'Inactiva' }}</div>

                <div class="mt-2 flex gap-3">
                    <form method="POST" action="{{ route('myadoptions.toggle', $post) }}">
                        @csrf
                        @method('PATCH')
                        <button class="underline">{{ $post->is_active ? 'Desactivar' : 'Activar' }}</button>
                    </form>

                    <a class="underline" href="{{ route('myadoptions.requests', $post) }}">Ver solicitudes</a>
                </div>
            </div>
        @empty
            <p>Aún no tienes publicaciones.</p>
        @endforelse
    </div>
</x-app-layout>
