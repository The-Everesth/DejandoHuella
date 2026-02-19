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
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1 4.5 4.5 0 11-4.814 6.98z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Dirección</p>
                                    <p class="text-gray-800">{{ $clinic->address }}</p>
                                </div>
                            </div>
                        @endif

                        @if($clinic->phone)
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.058.319.105.635.105.954 0 1.668-.728 3.157-1.882 4.122l1.548.773a1 1 0 01.54 1.06l-.74 4.435A1 1 0 015.153 19H3a1 1 0 01-1-1v-2.868a1 1 0 01.05-.196l3.75-9.375A1 1 0 013 7.5V3z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Teléfono</p>
                                    <p class="text-gray-800">{{ $clinic->phone }}</p>
                                </div>
                            </div>
                        @endif

                        @if($clinic->email)
                            <div class="mb-4 flex items-start gap-3">
                                <svg class="w-5 h-5 text-teal-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
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
                        @if($clinic->services->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($clinic->services as $service)
                                    <div class="border border-teal-200 rounded-lg p-4 hover:bg-teal-50 transition">
                                        <h3 class="font-semibold text-teal-700">{{ $service->name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $service->description }}</p>
                                        <div class="mt-3 flex items-center justify-between">
                                            @if($service->pivot->price)
                                                <span class="text-lg font-bold text-teal-600">
                                                    ${{ number_format($service->pivot->price, 2) }} {{ $service->pivot->currency ?? 'USD' }}
                                                </span>
                                            @endif
                                            @if($service->pivot->duration_minutes)
                                                <span class="text-sm text-gray-500">
                                                    {{ $service->pivot->duration_minutes }} min
                                                </span>
                                            @endif
                                        </div>
                                        @if($service->pivot->is_available)
                                            <div class="flex gap-2 mt-3">
                                                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                    Disponible
                                                </span>
                                                <a href="{{ route('appointments.create', [$clinic->id, $service->id]) }}" class="ml-auto px-3 py-1 bg-teal-600 text-white text-xs font-semibold rounded hover:bg-teal-700 transition">
                                                    Agendar
                                                </a>
                                            </div>
                                        @else
                                            <span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">
                                                No disponible
                                            </span>
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
