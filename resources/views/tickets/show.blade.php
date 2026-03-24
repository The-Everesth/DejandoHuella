@extends('layouts.app')

@section('content')
  <h2 class="text-xl font-bold mb-2">{{ $ticket->subject }}</h2>
  <div class="text-sm text-gray-600 mb-4">
    Prioridad: {{ $ticket->priority }} | Estado: <b>{{ $ticket->status }}</b>
  </div>

  <div class="p-4 border rounded mb-4">
    <b>Tu mensaje:</b>
    <div class="mt-2 whitespace-pre-line">{{ $ticket->message }}</div>
  </div>

  @if($ticket->admin_reply)
    <div class="p-4 border rounded">
      <b>Respuesta de administración:</b>
      <div class="mt-2 whitespace-pre-line">{{ $ticket->admin_reply }}</div>
      <div class="text-sm text-gray-600 mt-2">
        Respondido: {{ optional($ticket->answered_at)->format('Y-m-d H:i') }}
      </div>
    </div>
  @else
    <p class="text-sm text-gray-600">Aún no hay respuesta.</p>
  @endif
@endsection
