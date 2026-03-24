@extends('layouts.app')

@section('content')
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Mis mensajes</h2>
    <a class="underline" href="{{ route('tickets.create') }}">Enviar mensaje</a>
  </div>

  @if(session('success'))
    <div class="p-3 border rounded mb-3">{{ session('success') }}</div>
  @endif

  <div class="space-y-3">
    @forelse($tickets as $t)
      <div class="p-4 border rounded">
        <div class="flex justify-between">
          <div class="font-semibold">{{ $t->subject }}</div>
          <div class="text-sm">Estado: <b>{{ $t->status }}</b></div>
        </div>
        <div class="text-sm text-gray-600">Prioridad: {{ $t->priority }} | {{ $t->created_at->format('Y-m-d H:i') }}</div>
        <a class="underline" href="{{ route('tickets.show', $t) }}">Ver</a>
      </div>
    @empty
      <p>No has enviado mensajes.</p>
    @endforelse
  </div>
@endsection
