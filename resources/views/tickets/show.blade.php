
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-2">
  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 border-b pb-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 leading-tight break-words">
            {{ $ticket['subject'] }}
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
            {{ ucfirst($ticket['priority']) }}
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
            {{ ucfirst($ticket['status']) }}
          </span>
        </div>
      </div>

      <!-- Mensaje del usuario -->
      <div class="mb-6">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <span class="font-semibold text-gray-700">Tu mensaje</span>
            <span class="text-xs text-gray-400">
              @php
                $createdAt = $ticket['created_at'] ?? null;
                try {
                  $date = $createdAt ? \Carbon\Carbon::parse($createdAt) : null;
                } catch (Exception $e) { $date = null; }
              @endphp
              {{ $date ? $date->format('d/m/Y H:i') : '' }}
            </span>
          </div>
          <div class="text-gray-700 leading-relaxed whitespace-pre-line">
            {{ $ticket['message'] }}
          </div>
        </div>
      </div>

      <!-- Respuesta de administración -->
      @if(!empty($ticket['admin_reply']))
      <div class="mb-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow flex flex-col md:flex-row md:items-start gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <span class="font-semibold text-blue-800">Respuesta de administración</span>
              <span class="text-xs text-gray-400">
                @php
                  $answeredAt = $ticket['answered_at'] ?? null;
                  try {
                    $adate = $answeredAt ? (is_string($answeredAt) ? \Carbon\Carbon::parse($answeredAt) : $answeredAt) : null;
                  } catch (Exception $e) { $adate = null; }
                @endphp
                {{ $adate ? (is_string($adate) ? $adate : $adate->format('d/m/Y H:i')) : '' }}
              </span>
            </div>
            <div class="text-blue-900 leading-relaxed whitespace-pre-line">
              {{ $ticket['admin_reply'] }}
            </div>
          </div>
        </div>
      </div>
      @else
        <p class="text-sm text-gray-600">Aún no hay respuesta.</p>
      @endif

      <!-- Acciones -->
      <div class="flex justify-between items-center mt-8">
        <a href="{{ route('tickets.index') }}" class="text-blue-600 hover:underline text-sm font-medium">
          ← Volver a mis tickets
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
