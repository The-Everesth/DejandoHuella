<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Adopciones</h2>

    <div class="space-y-3">
        @forelse($posts as $post)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $post->title }}</div>
                <div class="text-sm text-gray-600">
                    Mascota: {{ $post->pet->name }} ({{ $post->pet->species }})
                </div>
                <a class="underline" href="{{ route('adoptions.show', $post) }}">Ver detalle</a>
            </div>
        @empty
            <p>No hay publicaciones activas.</p>
        @endforelse
    </div>
</x-app-layout>
