<x-app-layout>
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-teal-600 to-teal-800 p-6 text-white">
                    <h1 class="text-4xl font-bold">{{ $clinic['name'] ?? 'Clínica' }}</h1>
                    @if(!empty($ownerName))
                        <p class="text-teal-100 mt-2">Dr. {{ $ownerName }}</p>
                    @endif
                </div>

                <!-- Main content -->
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Clinic Info -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">Información</h2>

                        @if(!empty($clinic['address']))
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 8.05a4.95 4.95 0 119.9 0c0 2.866-2.91 6.021-4.233 7.278a1.03 1.03 0 01-1.434 0C7.96 14.07 5.05 10.916 5.05 8.05zm4.95-1.8a1.8 1.8 0 100 3.6 1.8 1.8 0 000-3.6z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Dirección</p>
                                    <p class="text-gray-800">{{ $clinic['address'] }}</p>
                                </div>
                            </div>
                        @endif

                        @if(!empty($clinic['phone']))
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.3 3.6c0-.72.58-1.3 1.3-1.3h2.15c.62 0 1.16.44 1.28 1.05l.58 2.9a1.3 1.3 0 01-.75 1.45l-1.05.42a11.9 11.9 0 005.33 5.33l.42-1.05a1.3 1.3 0 011.45-.75l2.9.58c.61.12 1.05.66 1.05 1.28v2.15c0 .72-.58 1.3-1.3 1.3h-1.2C8.43 17 2.3 10.87 2.3 3.6v0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Teléfono</p>
                                    <p class="text-gray-800">{{ $clinic['phone'] }}</p>
                                </div>
                            </div>
                        @endif

                        @if(!empty($clinic['email']))
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Email</p>
                                    <p class="text-gray-800">{{ $clinic['email'] }}</p>
                                </div>
                            </div>
                        @endif

                        @if(!empty($clinic['description']))
                            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-600 mb-2">Descripción</p>
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $clinic['description'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Services -->
                    <div>
                        <h2 class="text-2xl font-bold mb-4 text-gray-800">Servicios Disponibles</h2>
                        @if(!empty($clinic['servicesDetailed']) && count($clinic['servicesDetailed']) > 0)
                            <div class="space-y-3">
                                @foreach($clinic['servicesDetailed'] as $service)
                                    <div class="border border-teal-200 rounded-lg p-4 hover:bg-teal-50 transition">
                                        <h3 class="font-semibold text-teal-700">{{ $service['name'] ?? 'Servicio' }}</h3>
                                        @if(!empty($service['description']))
                                            <p class="text-sm text-gray-600 mt-1">{{ $service['description'] }}</p>
                                        @endif
                                    </div>
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
