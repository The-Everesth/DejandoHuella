<x-app-layout>
    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back button -->
            <a href="{{ route('services.index') }}" class="text-teal-700 hover:underline mb-4 inline-block">
                 Volver al listado
            </a>

            <!-- Service Details Card -->
            <div class="bg-white rounded-lg shadow p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $service->name ?? 'Servicio' }}</h1>
                
                <p class="text-gray-600 mb-6">{{ $service->description ?? 'Sin descripción disponible' }}</p>

                <!-- Clinics offering this service -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Clínicas que ofrecen este servicio</h2>

                    @if($service->clinics && $service->clinics->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($service->clinics as $c)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $c->name }}</h3>
                                    
                                    <p class="text-gray-600 text-sm mb-2">
                                         {{ $c->address_line ?? 'Dirección no disponible' }}
                                        @if(isset($c->city))
                                            , {{ $c->city }}
                                        @endif
                                        @if(isset($c->state))
                                            , {{ $c->state }}
                                        @endif
                                    </p>

                                    @if($c->user)
                                        <p class="text-gray-600 text-sm mb-4">
                                             <strong>Veterinario:</strong> {{ $c->user->name }}
                                        </p>
                                    @endif

                                    <a href="{{ route('appointments.create', [$c, $service]) }}" class="block w-full text-center bg-teal-700 text-white px-4 py-2 rounded-lg hover:bg-teal-800 transition font-medium">
                                        Solicitar cita
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                            <p class="text-gray-500">No hay clínicas disponibles para este servicio.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
