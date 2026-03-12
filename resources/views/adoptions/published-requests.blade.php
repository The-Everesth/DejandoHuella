<x-app-layout>
    <x-page-title
        title="Solicitudes recibidas de adopcion"
        subtitle="Aqui puedes revisar las solicitudes enviadas para las mascotas que publicaste."
    />

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($requests as $item)
            @php
                $status = strtolower((string) ($item['status'] ?? 'pendiente'));
                $isApproved = in_array($status, ['aprobada', 'approved'], true);
                $isRejected = in_array($status, ['rechazada', 'rejected'], true);

                $statusLabel = 'Pendiente';
                $statusClasses = 'bg-yellow-100 text-yellow-800';

                if ($isApproved) {
                    $statusLabel = 'Aprobada';
                    $statusClasses = 'bg-green-100 text-green-800';
                } elseif ($isRejected) {
                    $statusLabel = 'Rechazada';
                    $statusClasses = 'bg-red-100 text-red-800';
                }

                $createdAtLabel = 'Sin fecha';
                if (!empty($item['createdAt'])) {
                    try {
                        $createdAtLabel = \Carbon\Carbon::parse((string) $item['createdAt'])->format('d/m/Y H:i');
                    } catch (\Throwable $e) {
                        $createdAtLabel = (string) $item['createdAt'];
                    }
                }

                $hogarIntegrantes = $item['hogarIntegrantes'] ?? [];
                if (!is_array($hogarIntegrantes)) {
                    $hogarIntegrantes = [];
                }

                $requestId = (string) ($item['id'] ?? $item['_docId'] ?? '');
            @endphp

            <details class="rounded-lg border bg-white p-4 shadow-sm">
                <summary class="flex cursor-pointer list-none items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-500">Mascota</p>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $item['petName'] ?? 'Sin nombre' }}</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Solicitante: <span class="font-medium text-gray-900">{{ $item['applicantName'] ?? $item['nombreCompleto'] ?? 'Sin nombre' }}</span>
                        </p>
                        <p class="text-xs text-gray-500">Enviada: {{ $createdAtLabel }}</p>
                    </div>

                    <span class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold {{ $statusClasses }}">
                        {{ $statusLabel }}
                    </span>
                </summary>

                <div class="mt-4 grid grid-cols-1 gap-4 border-t pt-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Contacto</p>
                        <p class="mt-1 text-sm text-gray-700"><span class="font-medium">Email:</span> {{ $item['applicantEmail'] ?? 'No disponible' }}</p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Telefono:</span> {{ $item['telefono'] ?? 'No disponible' }}</p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Ciudad:</span> {{ $item['direccionCiudad'] ?? 'No disponible' }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Hogar</p>
                        <p class="mt-1 text-sm text-gray-700"><span class="font-medium">Vivienda:</span> {{ ucfirst((string) ($item['tipoVivienda'] ?? 'No disponible')) }}</p>
                        <p class="text-sm text-gray-700"><span class="font-medium">Patio o jardin:</span> {{ strtoupper((string) ($item['patioJardin'] ?? 'No')) }}</p>
                        <p class="text-sm text-gray-700">
                            <span class="font-medium">Integrantes:</span>
                            {{ !empty($hogarIntegrantes) ? implode(', ', $hogarIntegrantes) : 'No especificado' }}
                        </p>
                        @if(!empty($item['hogarIntegrantesOtros']))
                            <p class="text-sm text-gray-700"><span class="font-medium">Otros:</span> {{ $item['hogarIntegrantesOtros'] }}</p>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Experiencia</p>
                        <p class="mt-1 text-sm text-gray-700">
                            <span class="font-medium">Ha tenido mascotas antes:</span>
                            {{ strtoupper((string) ($item['tuvoMascotasAntes'] ?? 'No')) }}
                        </p>
                        @if(!empty($item['detalleMascotasAnteriores']))
                            <p class="text-sm text-gray-700">{{ $item['detalleMascotasAnteriores'] }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-700"><span class="font-medium">Experiencia previa:</span> {{ $item['experienciaMascotas'] ?? 'No especificada' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Otros animales</p>
                        <p class="mt-1 text-sm text-gray-700">
                            <span class="font-medium">Tiene otros animales:</span>
                            {{ strtoupper((string) ($item['tieneOtrosAnimales'] ?? 'No')) }}
                        </p>
                        @if(!empty($item['tiposOtrosAnimales']))
                            <p class="text-sm text-gray-700"><span class="font-medium">Tipos:</span> {{ $item['tiposOtrosAnimales'] }}</p>
                        @endif
                        @if(!empty($item['otrosAnimalesEsterilizados']))
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Esterilizados:</span>
                                {{ strtoupper((string) $item['otrosAnimalesEsterilizados']) }}
                            </p>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Motivacion</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $item['mensaje'] ?? 'Sin mensaje' }}</p>
                    </div>

                    @if($requestId !== '')
                        <div class="md:col-span-2 border-t pt-3">
                            <p class="mb-2 text-xs uppercase tracking-wide text-gray-500">Gestionar solicitud</p>
                            <div class="flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('requests.status', ['requestId' => $requestId]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="aprobada">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-full border border-green-300 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-100 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($isApproved) disabled @endif
                                    >
                                        Aprobar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('requests.status', ['requestId' => $requestId]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rechazada">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-full border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($isRejected) disabled @endif
                                    >
                                        Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </details>
        @empty
            <div class="rounded-lg border bg-white p-4 text-gray-600">
                Aun no has recibido solicitudes para tus publicaciones de adopcion.
            </div>
        @endforelse
    </div>
</x-app-layout>
