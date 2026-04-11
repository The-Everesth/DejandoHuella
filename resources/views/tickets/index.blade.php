@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Mis mensajes de soporte</h1>
      <p class="text-gray-500 text-sm mt-1">Consulta los mensajes enviados al equipo de administración y su estado.</p>
    </div>
    <a href="{{ route('tickets.create') }}"
      class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition">Enviar mensaje</a>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
      {{ session('success') }}
    </div>
  @endif

  @if($tickets->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 bg-white rounded-xl shadow border border-dashed border-gray-200">
      <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79V6.5A2.5 2.5 0 0 0 18.5 4h-13A2.5 2.5 0 0 0 3 6.5v11A2.5 2.5 0 0 0 5.5 20h7.29" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 17h6m0 0-2-2m2 2-2 2" />
      </svg>
      <div class="text-gray-600 text-lg font-semibold mb-2">Aún no has enviado mensajes de soporte.</div>
      <div class="text-gray-400 mb-6 text-center max-w-xs">Si tienes alguna duda, problema o sugerencia, puedes contactar al equipo de administración.</div>
      <a href="{{ route('tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg shadow transition">Enviar mensaje</a>
    </div>
  @else
    <div class="space-y-4">
      @foreach($tickets as $t)
        <div class="bg-white rounded-xl shadow border p-5 flex flex-col gap-2">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
              <span class="text-lg font-bold text-gray-800 truncate">{{ $t['subject'] ?? '' }}</span>
              @if(!empty($t['status']))
                @php
                  $statusColors = [
                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                    'en proceso' => 'bg-blue-100 text-blue-800',
                    'resuelto' => 'bg-green-100 text-green-800',
                  ];
                  $status = strtolower($t['status']);
                  $badgeClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-600';
                  $statusLabel = ucfirst($t['status']);
                @endphp
                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ $statusLabel }}</span>
              @endif
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium
                @if(($t['priority'] ?? '')==='alta') bg-red-100 text-red-700 @elseif(($t['priority'] ?? '')==='media') bg-yellow-100 text-yellow-800 @else bg-gray-100 text-gray-600 @endif">
                {{ ucfirst($t['priority'] ?? '') }}
              </span>
              <span class="text-xs text-gray-400">
                @php
                  $createdAt = $t['created_at'] ?? null;
                  try {
                    $date = $createdAt ? \Carbon\Carbon::parse($createdAt) : null;
                  } catch (Exception $e) { $date = null; }
                @endphp
                {{ $date ? $date->format('d/m/Y H:i') : '' }}
              </span>
            </div>
          </div>
          <div class="text-gray-600 text-sm truncate">{{ \Illuminate\Support\Str::limit($t['message'] ?? '', 120) }}</div>
          <div class="flex justify-end">
            <a href="{{ route('tickets.show', $t['id']) }}" class="text-blue-600 hover:underline text-sm font-medium">Ver detalle</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
