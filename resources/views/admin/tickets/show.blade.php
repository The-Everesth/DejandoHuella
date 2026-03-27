@extends('layouts.app')

@section('content')
  <h2 class="text-xl font-bold mb-2">{{ $ticket['subject'] ?? '' }}</h2>

  @if(session('success'))
    <div class="p-3 border rounded mb-3">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="p-3 border rounded mb-3">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  <div class="text-sm text-gray-600 mb-4">
    De: <b>{{ $ticket['user_id'] ?? '—' }}</b> |
    Prioridad: <b>{{ $ticket['priority'] ?? '—' }}</b> |
    Estado: <b>{{ $ticket['status'] ?? '—' }}</b>
  </div>

  <div class="p-4 border rounded mb-4">
    <b>Mensaje:</b>
    <div class="mt-2 whitespace-pre-line">{{ $ticket['message'] ?? '' }}</div>
  </div>

  @if(!empty($ticket['admin_reply']))
    <div class="p-4 border rounded mb-4">
      <b>Respuesta actual:</b>
      <div class="mt-2 whitespace-pre-line">{{ $ticket['admin_reply'] }}</div>
      <div class="text-sm text-gray-600 mt-2">
        Por: {{ $ticket['answered_by'] ?? '—' }} |
        @php
          $answeredAt = $ticket['answered_at'] ?? null;
          try { $date = $answeredAt ? \Carbon\Carbon::parse($answeredAt) : null; } catch (Exception $e) { $date = null; }
        @endphp
        {{ $date ? $date->format('Y-m-d H:i') : '' }}
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.tickets.reply', $ticket['id']) }}" class="space-y-3 mb-4">
    @csrf
    <textarea class="border rounded p-2 w-full" name="admin_reply" rows="5" placeholder="Escribe tu respuesta..." required>{{ old('admin_reply', $ticket['admin_reply'] ?? '') }}</textarea>
    <button class="border rounded px-4 py-2">Guardar respuesta</button>
  </form>

  <form method="POST" action="{{ route('admin.tickets.close', $ticket['id']) }}">
    @csrf
    <button class="underline">Cerrar ticket</button>
  </form>
@endsection
