<x-app-layout>
  <h2 class="text-xl font-bold mb-4">Enviar mensaje a administración</h2>

  @if($errors->any())
    <div class="p-3 border rounded mb-3">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('tickets.store') }}" class="space-y-3">
    @csrf
    <input class="border rounded p-2 w-full" name="subject" placeholder="Asunto" value="{{ old('subject') }}" required>

    <select class="border rounded p-2 w-full" name="priority" required>
      <option value="baja" {{ old('priority')==='baja'?'selected':'' }}>Baja</option>
      <option value="media" {{ old('priority','media')==='media'?'selected':'' }}>Media</option>
      <option value="alta" {{ old('priority')==='alta'?'selected':'' }}>Alta</option>
    </select>

    <textarea class="border rounded p-2 w-full" name="message" rows="6" placeholder="Escribe tu mensaje..." required>{{ old('message') }}</textarea>

    <button class="border rounded px-4 py-2">Enviar</button>
  </form>
</x-app-layout>
