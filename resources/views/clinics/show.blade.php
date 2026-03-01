<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-teal-600 to-teal-800 p-6 text-white">
                    <h1 class="text-4xl font-bold">{{ $clinic->name }}</h1>
                    @if($clinic->user)
                        <p class="text-teal-100 mt-2">Dr. {{ $clinic->user->name }}</p>
                    @endif
                </div>

                <!-- Main content -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Clinic Info -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">Información</h2>

                        @if($clinic->address)
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-6-5.38-6-10a6 6 0 1112 0c0 4.62-6 10-6 10z"></path>
                                    <circle cx="12" cy="11" r="2.5"></circle>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Dirección</p>
                                    <p class="text-gray-800">{{ $clinic->address }}</p>
                                </div>
                            </div>
                        @endif

                        @if($clinic->phone)
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 16.92v3a2 2 0 01-2.18 2 19.8 19.8 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.8 19.8 0 012.08 4.2 2 2 0 014.06 2h3a2 2 0 012 1.72c.12.95.33 1.89.64 2.79a2 2 0 01-.45 2.11L8 10a16 16 0 006 6l1.38-1.25a2 2 0 012.11-.45c.9.31 1.84.52 2.79.64A2 2 0 0122 16.92z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Teléfono</p>
                                    <p class="text-gray-800">{{ $clinic->phone }}</p>
                                </div>
                            </div>
                        @endif

                        @if($clinic->email)
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 7l-10 7L2 7"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Email</p>
                                    <p class="text-gray-800">{{ $clinic->email }}</p>
                                </div>
                            </div>
                        @endif

                        @if($clinic->description)
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 mb-2">Descripción</p>
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $clinic->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Services -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">Servicios Disponibles</h2>
                        @php
                            // $clinic->serviceIds: array of Firestore service IDs
                            // $allServices: array of all Firestore services, indexed by id
                            $hasServices = isset($clinic->serviceIds) && is_array($clinic->serviceIds) && count($clinic->serviceIds) > 0;
                        @endphp
                        @if($hasServices)
                            <div class="space-y-3 max-h-96 overflow-y-auto">
                                @foreach($clinic->serviceIds as $serviceId)
                                    @php $service = $allServices[$serviceId] ?? null; @endphp
                                    @if($service)
                                        <div class="border border-teal-200 rounded-lg p-4 hover:bg-teal-50 transition">
                                            <h3 class="font-semibold text-teal-700">{{ $service['name'] }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">{{ $service['description'] ?? '' }}</p>
                                            <div class="mt-3 flex items-center justify-between">
                                                @if(isset($service['price']))
                                                    <span class="text-lg font-bold text-teal-600">
                                                        ${{ number_format($service['price'], 2) }} {{ $service['currency'] ?? 'USD' }}
                                                    </span>
                                                @endif
                                                @if(isset($service['durationMinutes']))
                                                    <span class="text-sm text-gray-500">
                                                        {{ $service['durationMinutes'] }} min
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!isset($service['is_active']) || $service['is_active'])
                                                <div class="flex gap-2 mt-3">
                                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                        Disponible
                                                    </span>
                                                    <a href="{{ route('appointments.create', [
                                                        'clinic' => $clinic->id,
                                                        'service' => $service['id']
                                                    ]) }}">
                                                        <button class="ml-auto bg-teal-600 text-white px-4 py-2 rounded-lg hover:bg-teal-700 transition text-sm font-medium">
                                                            Solicitar cita
                                                        </button>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">
                                                    No disponible
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">Esta clínica aún no ha publicado servicios.</p>
                        @endif
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="bg-gray-50 px-6 py-4 border-t flex gap-3">
                    <a href="{{ route('services.index') }}" class="flex-1 text-center bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition font-medium">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
