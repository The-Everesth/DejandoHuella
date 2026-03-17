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

    <div class="mb-4 rounded-lg border bg-white p-4 shadow-sm">
        <p class="mb-3 text-sm font-semibold tracking-wide text-gray-700">Filtrar solicitudes</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <div>
                <label for="publishedRequestsTypeFilter" class="mb-1 block text-sm font-medium text-gray-700">Tipo</label>
                <select
                    id="publishedRequestsTypeFilter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                >
                    <option value="">Todos</option>
                    <option value="perro">Perro</option>
                    <option value="gato">Gato</option>
                    <option value="conejo">Conejo</option>
                    <option value="ave">Ave</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div>
                <label for="publishedRequestsSexFilter" class="mb-1 block text-sm font-medium text-gray-700">Sexo</label>
                <select
                    id="publishedRequestsSexFilter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                >
                    <option value="">Todos</option>
                    <option value="hembra">Hembra</option>
                    <option value="macho">Macho</option>
                </select>
            </div>

            <div>
                <label for="publishedRequestsStatusFilter" class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                <select
                    id="publishedRequestsStatusFilter"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:outline-none focus:ring-2 focus:ring-teal-700"
                >
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($requests as $item)
            @php
                $status = strtolower((string) ($item['status'] ?? 'pendiente'));
                $isApproved = in_array($status, ['aprobada', 'approved'], true);
                $isRejected = in_array($status, ['rechazada', 'rejected'], true);
                $isCancelled = in_array($status, ['cancelada', 'cancelled', 'canceled'], true);
                $statusValue = 'pendiente';

                $statusLabel = 'Pendiente';
                $statusClasses = 'bg-yellow-100 text-yellow-800';

                if ($isApproved) {
                    $statusLabel = 'Aprobada';
                    $statusValue = 'aprobada';
                    $statusClasses = 'bg-green-100 text-green-800';
                } elseif ($isRejected) {
                    $statusLabel = 'Rechazada';
                    $statusValue = 'rechazada';
                    $statusClasses = 'bg-red-100 text-red-800';
                } elseif ($isCancelled) {
                    $statusLabel = 'Cancelada';
                    $statusValue = 'cancelada';
                    $statusClasses = 'bg-slate-200 text-slate-700';
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
                $petTypeValue = strtolower(trim((string) ($item['petType'] ?? $item['tipoAnimal'] ?? '')));
                $petSexValue = strtolower(trim((string) ($item['petSex'] ?? $item['sexo'] ?? '')));

                if (! in_array($petTypeValue, ['perro', 'gato', 'conejo', 'ave', 'otro'], true)) {
                    $petTypeValue = '';
                }

                if (! in_array($petSexValue, ['hembra', 'macho'], true)) {
                    $petSexValue = '';
                }

                $petTypeLabel = $petTypeValue !== '' ? ucfirst($petTypeValue) : 'No especificado';
                $petSexLabel = $petSexValue !== '' ? ucfirst($petSexValue) : 'No especificado';

            @endphp

            <details
                class="published-request-card rounded-lg border bg-white p-4 shadow-sm"
                data-status="{{ $statusValue }}"
                data-type="{{ $petTypeValue }}"
                data-sex="{{ $petSexValue }}"
            >
                <summary class="flex cursor-pointer list-none items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-500">Mascota</p>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $item['petName'] ?? 'Sin nombre' }}</h2>
                        <p class="text-xs text-gray-500">Tipo: {{ $petTypeLabel }} | Sexo: {{ $petSexLabel }}</p>
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

                    @if($requestId !== '' && ! $isCancelled)
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

    <div id="publishedRequestsNoMatches" class="mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
        No se encontraron solicitudes con esos filtros.
    </div>

    <script>
        (function () {
            const statusFilter = document.getElementById('publishedRequestsStatusFilter');
            const typeFilter = document.getElementById('publishedRequestsTypeFilter');
            const sexFilter = document.getElementById('publishedRequestsSexFilter');
            const noMatches = document.getElementById('publishedRequestsNoMatches');
            const cards = Array.from(document.querySelectorAll('.published-request-card'));

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function applyPublishedRequestsFilters() {
                if (cards.length === 0) {
                    if (noMatches) {
                        noMatches.classList.add('hidden');
                    }
                    return;
                }

                const selectedStatus = normalize(statusFilter?.value);
                const selectedType = normalize(typeFilter?.value);
                const selectedSex = normalize(sexFilter?.value);
                let visible = 0;

                cards.forEach((card) => {
                    const cardStatus = normalize(card.dataset.status);
                    const cardType = normalize(card.dataset.type);
                    const cardSex = normalize(card.dataset.sex);

                    const matchesStatus = !selectedStatus || cardStatus === selectedStatus;
                    const matchesType = !selectedType || cardType === selectedType;
                    const matchesSex = !selectedSex || cardSex === selectedSex;
                    const showCard = matchesStatus && matchesType && matchesSex;

                    card.classList.toggle('hidden', !showCard);
                    if (showCard) {
                        visible += 1;
                    }
                });

                if (noMatches) {
                    noMatches.classList.toggle('hidden', visible > 0);
                }
            }

            if (statusFilter) {
                statusFilter.addEventListener('change', applyPublishedRequestsFilters);
            }

            if (typeFilter) {
                typeFilter.addEventListener('change', applyPublishedRequestsFilters);
            }

            if (sexFilter) {
                sexFilter.addEventListener('change', applyPublishedRequestsFilters);
            }

            applyPublishedRequestsFilters();
        })();
    </script>
</x-app-layout>
