<x-app-layout>
    <h2 class="text-xl font-bold mb-2">Solicitudes</h2>
    <p class="mb-4">Publicación: <b>{{ $post->title }}</b> — Mascota: {{ $post->pet->name }}</p>

    <div class="space-y-3">
        @forelse($post->requests as $req)
            <div class="p-4 border rounded">
                <div><b>Solicitante:</b> {{ $req->applicant->name }} ({{ $req->applicant->email }})</div>
                <div><b>Estado:</b> {{ $req->status }}</div>
                @if($req->message)
                    <div class="mt-2"><b>Mensaje:</b> {{ $req->message }}</div>
                @endif

                @if($req->status === 'pendiente')
                    <div class="mt-3 flex gap-3">
                        <form method="POST" action="{{ route('requests.status', $req) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="aprobada">
                            <button class="underline">Aprobar</button>
                        </form>

                        <form method="POST" action="{{ route('requests.status', $req) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rechazada">
                            <button class="underline">Rechazar</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p>No hay solicitudes aún.</p>
        @endforelse
    </div>
</x-app-layout>
