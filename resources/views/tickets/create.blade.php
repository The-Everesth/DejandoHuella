@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto py-10">
  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Contactar soporte</h1>
    <p class="text-gray-500 text-sm">Cuéntanos qué problema tienes, qué duda surgió o qué sugerencia deseas enviar. El equipo revisará tu mensaje lo antes posible.</p>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
      @foreach($errors->all() as $e)
        <div>{{ $e }}</div>
      @endforeach
    </div>
  @endif

  <div class="bg-white rounded-xl shadow border p-8">
    <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
      @csrf
      <div>
        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1">Asunto</label>
        <input id="subject" name="subject" type="text" class="border border-gray-300 rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition" placeholder="Asunto del mensaje" value="{{ old('subject') }}" required>
      </div>
      <div>
        <label for="priority" class="block text-sm font-semibold text-gray-700 mb-1">Prioridad</label>
        <select id="priority" name="priority" class="border border-gray-300 rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition" required>
          <option value="baja" {{ old('priority')==='baja'?'selected':'' }}>Baja</option>
          <option value="media" {{ old('priority','media')==='media'?'selected':'' }}>Media</option>
          <option value="alta" {{ old('priority')==='alta'?'selected':'' }}>Alta</option>
        </select>
      </div>
      <div>
        <label for="message" class="block text-sm font-semibold text-gray-700 mb-1">Mensaje</label>
        <textarea id="message" name="message" rows="6" class="border border-gray-300 rounded-lg p-3 w-full focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition" placeholder="Escribe tu mensaje..." required>{{ old('message') }}</textarea>
      </div>
      <div class="pt-2">
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow transition">Enviar mensaje</button>
      </div>
    </form>
  </div>
</div>
@endsection
