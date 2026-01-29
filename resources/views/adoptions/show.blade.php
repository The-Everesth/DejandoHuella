<x-app-layout>
    <h2 class="text-xl font-bold mb-2">{{ $post->title }}</h2>

    <p class="mb-2"><b>Mascota:</b> {{ $post->pet->name }} ({{ $post->pet->species }})</p>
    <p class="mb-2"><b>Descripción:</b> {{ $post->description }}</p>

    @if($post->requirements)
        <p class="mb-4"><b>Requisitos:</b> {{ $post->requirements }}</p>
    @endif

    @if(auth()->id() !== $post->created_by)
        <form method="POST" action="{{ route('adoptions.request.store', $post) }}" class="space-y-2">
            @csrf
            <textarea name="message" class="w-full border rounded p-2" rows="3" placeholder="Mensaje opcional..."></textarea>
            <button class="px-4 py-2 border rounded">Solicitar adopción</button>
        </form>
    @else
        <p class="text-sm text-gray-600">Esta publicación es tuya.</p>
    @endif

</x-app-layout>
