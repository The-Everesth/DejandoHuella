@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50 py-8 px-2">
  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
      <!-- Botón volver -->
      <div class="mb-6">
        <a href="{{ route('admin.tickets.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-lg px-4 py-2 shadow-sm transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
          Volver a la lista
        </a>
      </div>
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b pb-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight break-words">
            {{ $ticket['subject'] ?? '' }}
          </h1>
        </div>
        <div class="flex flex-wrap gap-2 sm:justify-end">
          {{-- Prioridad Badge --}}
          @php
            $priority = strtolower($ticket['priority'] ?? '');
            $priorityColors = [
              'alta' => 'bg-red-100 text-red-700 border border-red-200',
              'media' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
              'baja' => 'bg-gray-100 text-gray-600 border border-gray-200',
            ];
            $priorityClass = $priorityColors[$priority] ?? 'bg-gray-100 text-gray-600 border border-gray-200';
          @endphp
          <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $priorityClass }}">
            {{ ucfirst($ticket['priority'] ?? '—') }}
          </span>
          {{-- Estado Badge --}}
          @php
            $status = strtolower($ticket['status'] ?? '');
            $statusColors = [
              'pendiente' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
              'visto' => 'bg-blue-100 text-blue-800 border border-blue-200',
              'respondido' => 'bg-green-100 text-green-800 border border-green-200',
              'cerrado' => 'bg-gray-200 text-gray-600 border border-gray-300',
            ];
            $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-600 border border-gray-200';
          @endphp
          <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
            {{ ucfirst($ticket['status'] ?? '—') }}
          </span>
        </div>
      </div>

      <!-- Metadata -->
      <div class="flex flex-wrap items-center gap-3 mb-6 text-sm text-gray-600">
        <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-200 rounded px-2 py-0.5">
          <span class="font-semibold">De:</span>
          <span class="font-mono text-xs">{{ $ticket['user_id'] ?? '—' }}</span>
        </span>
        @if(!empty($ticket['created_at']))
        <span class="inline-flex items-center gap-1 bg-gray-100 border border-gray-200 rounded px-2 py-0.5">
          <span class="font-semibold">Enviado:</span>
          <span class="text-xs">{{ \Carbon\Carbon::parse($ticket['created_at'])->format('d/m/Y H:i') }}</span>
        </span>
        @endif
      </div>

      <!-- Mensaje original -->
      <div class="mb-6">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <span class="font-semibold text-gray-700">Mensaje original</span>
          </div>
          <div class="text-gray-700 leading-relaxed whitespace-pre-line">
            {{ $ticket['message'] ?? '' }}
          </div>
        </div>
      </div>

      <!-- Respuesta actual (si existe) -->
      @if(!empty($ticket['admin_reply']))
      <div class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow flex flex-col md:flex-row md:items-start gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <span class="font-semibold text-blue-800">Respuesta actual</span>
              <span class="text-xs text-gray-400">
                Por: <span class="font-mono">{{ $ticket['answered_by'] ?? '—' }}</span>
                @php
                  $answeredAt = $ticket['answered_at'] ?? null;
                  try { $date = $answeredAt ? \Carbon\Carbon::parse($answeredAt) : null; } catch (Exception $e) { $date = null; }
                @endphp
                {{ $date ? '| ' . $date->format('d/m/Y H:i') : '' }}
              </span>
            </div>
            <div class="text-blue-900 leading-relaxed whitespace-pre-line">
              {{ $ticket['admin_reply'] }}
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Gestión del ticket -->
      <div class="mb-2">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-md">
          @if(session('success'))
            <div class="mb-3 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
              {{ session('success') }}
            </div>
          @endif
          @if($errors->any())
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
              @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
          @endif
          <form method="POST" action="{{ route('admin.tickets.reply', $ticket['id']) }}" class="space-y-4">
            @csrf
            <label for="admin_reply" class="block font-semibold text-gray-700 mb-1">Editar respuesta</label>
            <textarea id="admin_reply" name="admin_reply" rows="5" required
              class="block w-full rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 p-3 text-gray-800 bg-gray-50 resize-y shadow-sm transition"
              placeholder="Escribe tu respuesta...">{{ old('admin_reply', $ticket['admin_reply'] ?? '') }}</textarea>
            <div class="flex flex-col sm:flex-row gap-3 mt-2">
              <button type="submit"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                Guardar respuesta
              </button>
              <form method="POST" action="{{ route('admin.tickets.close', $ticket['id']) }}" class="sm:ml-auto">
                @csrf
                <button type="submit"
                  class="inline-block bg-red-50 hover:bg-red-100 text-red-700 font-semibold px-6 py-2 rounded-lg border border-red-200 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-red-300 focus:ring-offset-2">
                  Cerrar ticket
                </button>
              </form>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
