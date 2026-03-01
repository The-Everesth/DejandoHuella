<x-app-layout>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Servicios Médicos Veterinarios</h1>
        <p class="text-gray-600 mt-2">Encuentra clínicas y veterinarios especializados en los servicios que necesitas</p>
      </header>

      <div class="bg-white p-6 rounded-lg shadow mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar clínica</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o dirección..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Servicio</label>
            <select name="service_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500">
              <option value="">Todos los servicios</option>
              @forelse($services as $service)
                <option value="{{ $service->id }}" @selected($serviceId == $service->id)>
                  {{ $service->name ?? $service->title ?? 'Sin nombre' }}
                </option>
              @empty
                <option disabled>No hay servicios disponibles</option>
              @endforelse
            </select>
          </div>

          <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-teal-700 text-white px-4 py-2 rounded-lg hover:bg-teal-800 transition">
              Filtrar
            </button>
            <a href="{{ route('services.index') }}" class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition text-center">
              Limpiar
            </a>
          </div>
        </form>
      </div>

      @if($clinics->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @forelse($clinics as $clinic)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
              <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $clinic->name }}</h3>

                <p class="text-gray-600 text-sm mb-4">
                  Ubicacion: {{ $clinic->address ?? 'Dirección no disponible' }}
                </p>

                @if($clinic->services && $clinic->services->count())
                  <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Servicios:</p>
                    <div class="flex flex-wrap gap-2">
                      @foreach($clinic->services as $service)
                        <span class="inline-block bg-teal-100 text-teal-700 px-3 py-1 rounded-full text-xs font-semibold">
                          {{ $service->name ?? $service->title ?? 'Servicio' }}
                        </span>
                      @endforeach
                    </div>
                  </div>
                @endif

                @if($clinic->user)
                  <div class="mb-4 p-3 bg-gray-50 rounded">
                    <p class="text-sm text-gray-700">
                      <strong>Veterinario:</strong> {{ $clinic->user->name }}
                    </p>
                  </div>
                @endif

                <a href="{{ route('clinics.show', $clinic->firestore_id ?? $clinic['id'] ?? $clinic->id ?? '') }}" class="block w-full text-center bg-teal-700 text-white px-4 py-2 rounded-lg hover:bg-teal-800 transition font-medium">
                  Ver detalles
                </a>
              </div>
            </div>
          @empty
            <div class="col-span-full text-center py-12">
              <p class="text-gray-500 text-lg">No hay clínicas disponibles con los filtros seleccionados.</p>
            </div>
          @endforelse
        </div>

        @if($clinics->hasPages())
          <div class="mt-8">
            {{ $clinics->links() }}
          </div>
        @endif
      @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
          <p class="text-blue-800 text-lg">No hay clínicas registradas en este momento.</p>
          <p class="text-blue-600 mt-2">Por favor intenta más tarde o ajusta tus filtros.</p>
        </div>
      @endif
    </div>
  </div>
</x-app-layout>
