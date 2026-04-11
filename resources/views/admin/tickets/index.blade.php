@extends('layouts.app')

@section('content')
    <x-page-title title="Tickets" subtitle="Mensajes enviados por usuarios." />

    <x-card class="mb-4">
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap gap-2 items-center">
              @php
                  $base = request()->except('page','status');

                  $active = "inline-flex items-center gap-2 px-4 py-2 rounded-full font-extrabold border transition bg-slate-900 text-white border-slate-900";
                  $inactive = "inline-flex items-center gap-2 px-4 py-2 rounded-full font-extrabold border transition bg-white text-slate-900 border-slate-200 hover:bg-slate-50";
              @endphp

              <a class="{{ (($status ?? 'pendiente') === 'pendiente') ? $active : $inactive }}"
                href="{{ route('admin.tickets.index', array_merge($base, ['status' => 'pendiente'])) }}">
                  Pendientes
                  @if(isset($pendingCount))
                      <span class="px-2 py-0.5 rounded-full bg-white/20 text-white font-black">
                          {{ $pendingCount }}
                      </span>
                  @endif
              </a>

              <a class="{{ (($status ?? '') === 'visto') ? $active : $inactive }}"
                href="{{ route('admin.tickets.index', array_merge($base, ['status' => 'visto'])) }}">
                  Vistos
              </a>

              <a class="{{ (($status ?? '') === 'respondido') ? $active : $inactive }}"
                href="{{ route('admin.tickets.index', array_merge($base, ['status' => 'respondido'])) }}">
                  Respondidos
              </a>

              <a class="{{ (($status ?? '') === 'cerrado') ? $active : $inactive }}"
                href="{{ route('admin.tickets.index', array_merge($base, ['status' => 'cerrado'])) }}">
                  Cerrados
              </a>

              <a class="{{ (($status ?? '') === 'all') ? $active : $inactive }}"
                href="{{ route('admin.tickets.index', array_merge($base, ['status' => 'all'])) }}">
                  Todos
              </a>
          </div>



            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <input type="hidden" name="status" value="{{ $status ?? 'pendiente' }}">

                <div class="md:col-span-2">
                    <label class="font-bold text-gray-800">Buscar</label>
                    <input name="q" value="{{ $q }}" class="mt-2 w-full border rounded-xl p-3" placeholder="Asunto o contenido...">
                </div>

                <div>
                    <label class="font-bold text-gray-800">Prioridad</label>
                    <select name="priority" class="mt-2 w-full border rounded-xl p-3">
                        <option value="" @selected(empty($priority))>Todas</option>
                        <option value="baja" @selected(($priority ?? '')==='baja')>Baja</option>
                        <option value="media" @selected(($priority ?? '')==='media')>Media</option>
                        <option value="alta" @selected(($priority ?? '')==='alta')>Alta</option>
                    </select>
                </div>

                <div class="flex gap-2 md:col-span-2">
                    <x-button type="submit" variant="soft" class="w-full">Filtrar</x-button>

                    <a href="{{ route('admin.tickets.index', ['status' => ($status ?? 'pendiente')]) }}" class="w-full">
                        <x-button variant="outline" class="w-full">Limpiar</x-button>
                    </a>
                </div>
            </form>
        </div>
    </x-card>

    <x-card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-slate-600">
                        <th class="p-4 font-bold">Ticket</th>
                        <th class="p-4 font-bold">Usuario</th>
                        <th class="p-4 font-bold">Prioridad</th>
                        <th class="p-4 font-bold">Estado</th>
                        <!-- <th class="p-4 font-bold text-right">Acción</th> -->
                    </tr>
                </thead>

                <tbody class="divide-y">
                  @if($tickets->count())
                      @foreach($tickets as $t)
                          @php
                              // Prioridad
                              $pClass = 'bg-gray-100 text-gray-800';
                              if (($t['priority'] ?? '') === 'baja') $pClass = 'bg-green-100 text-green-900';
                              if (($t['priority'] ?? '') === 'media') $pClass = 'bg-amber-100 text-amber-900';
                              if (($t['priority'] ?? '') === 'alta') $pClass = 'bg-red-100 text-red-900';

                              // Estado
                              $sClass = 'bg-gray-100 text-gray-800';
                              if (($t['status'] ?? '') === 'pendiente') $sClass = 'bg-amber-100 text-amber-900';
                              if (($t['status'] ?? '') === 'visto') $sClass = 'bg-blue-100 text-blue-900';
                              if (($t['status'] ?? '') === 'respondido') $sClass = 'bg-green-100 text-green-900';
                              if (($t['status'] ?? '') === 'cerrado') $sClass = 'bg-gray-200 text-gray-900';
                          @endphp

                          <tr class="hover:bg-slate-50">
                              <td class="p-4">
                                  <div class="font-extrabold text-slate-900">{{ $t['subject'] ?? '' }}</div>
                                  <div class="text-slate-600 line-clamp-1">{{ $t['message'] ?? '' }}</div>
                                  <div class="text-xs text-slate-500 mt-1">
                                    @php
                                      $createdAt = $t['created_at'] ?? null;
                                      try {
                                        $date = $createdAt ? \Carbon\Carbon::parse($createdAt) : null;
                                      } catch (Exception $e) { $date = null; }
                                    @endphp
                                    {{ $date ? $date->format('Y-m-d H:i') : '' }}
                                  </div>
                              </td>

                              <td class="p-4 text-slate-700">
                                  <div class="font-bold">{{ $t['user_id'] ?? '—' }}</div>
                              </td>

                              <td class="p-4">
                                  <span class="px-3 py-1 rounded-full font-bold {{ $pClass }}">
                                      {{ strtoupper($t['priority'] ?? '—') }}
                                  </span>
                              </td>

                              <td class="p-4">
                                  <span class="px-3 py-1 rounded-full font-bold {{ $sClass }}">
                                      {{ strtoupper($t['status'] ?? '—') }}
                                  </span>
                              </td>
                              <td class="p-4 text-right">
                                  <a class="inline-flex items-center justify-center px-4 py-2 rounded-full font-bold border transition bg-white text-slate-900 border-slate-200 hover:bg-slate-50"
                                    href="{{ route('admin.tickets.show', $t['id']) }}">
                                      Ver
                                  </a>
                              </td>
                          </tr>
                      @endforeach
                  @else
                      <tr>
                          <td class="p-6 text-center text-slate-600" colspan="5">
                              No hay tickets en esta sección.
                          </td>
                      </tr>
                  @endif
              </tbody>

            </table>
        </div>

        <div class="p-4">
            {{ $tickets->links() }}
        </div>
    </x-card>
@endsection
