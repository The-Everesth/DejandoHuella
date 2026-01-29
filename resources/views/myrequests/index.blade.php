<x-app-layout>
    <h2 class="text-xl font-bold mb-4">Mis solicitudes</h2>

    <div class="space-y-3">
        @forelse($requests as $req)
            <div class="p-4 border rounded">
                <div class="font-semibold">{{ $req->post->title }}</div>
                <div>Mascota: {{ $req->post->pet->name }}</div>
                <div>Estado: <b>{{ $req->status }}</b></div>
            </div>
        @empty
            <p>No has enviado solicitudes.</p>
        @endforelse
    </div>
</x-app-layout>
