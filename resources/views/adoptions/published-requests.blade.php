
@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-2 text-gray-800">Solicitudes recibidas de adopción</h1>
    <p class="mb-6 text-gray-500">Aquí puedes revisar las solicitudes enviadas para las mascotas que publicaste.</p>

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
                        $createdAtLabel = \Carbon\Carbon::parse((string) $item['createdAt'])->format('d M Y, H:i');
                    } catch (\Throwable $e) {
                        $createdAtLabel = (string) $item['createdAt'];
                    }
                }

                $hogarIntegrantes = $item['hogarIntegrantes'] ?? [];
                if (!is_array($hogarIntegrantes)) {
                    $hogarIntegrantes = [];
                }

                $requestId = (string) ($item['id'] ?? $item['_docId'] ?? '');
                $requestCodeSource = strtoupper(trim((string) ($requestId !== '' ? $requestId : 'SINID')));
                $requestCodeCompact = (string) preg_replace('/[^A-Z0-9]/', '', $requestCodeSource);
                if ($requestCodeCompact === '') {
                    $requestCodeCompact = 'SINID';
                }
                $requestCodeShort = substr($requestCodeCompact, -6);
                $requestCodeLabel = 'Solicitud #' . $requestCodeShort;

                $petName = (string) ($item['petName'] ?? 'Sin nombre');
                $applicantName = (string) ($item['applicantName'] ?? $item['nombreCompleto'] ?? 'Sin nombre');
                $contactEmail = (string) ($item['applicantEmail'] ?? 'No disponible');
                $contactPhone = (string) ($item['telefono'] ?? 'No disponible');
                $city = (string) ($item['direccionCiudad'] ?? 'No disponible');

                $requestMessage = trim((string) ($item['mensaje'] ?? ''));
                if ($requestMessage === '') {
                    $requestMessage = 'Sin mensaje';
                }

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
                $detailId = 'publishedRequestDetail_' . md5(($requestId !== '' ? $requestId : $petName . $applicantName));
                $notePanelId = 'publishedRequestNotePanel_' . md5(($requestId !== '' ? $requestId : $petName . $applicantName));
                $noteTextareaId = 'publishedRequestNoteInput_' . md5(($requestId !== '' ? $requestId : $petName . $applicantName));
                $reviewerNote = trim((string) ($item['reviewerNote'] ?? ''));
            @endphp

            <article
                class="published-request-card rounded-xl border border-gray-200 bg-white p-3 shadow-sm"
                data-status="{{ $statusValue }}"
                data-type="{{ $petTypeValue }}"
                data-sex="{{ $petSexValue }}"
            >
                <div class="published-request-layout flex flex-col gap-3">
                    <div class="min-w-0 flex-1">
                        <h2 class="break-all text-lg font-semibold leading-none text-gray-800 md:text-xl">{{ $requestCodeLabel }}</h2>
                        <p class="mt-1 text-sm text-gray-500 md:text-base">Solicitud de adopción</p>

                        <div class="mt-2 flex items-center gap-1 text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v3m8-3v3M3.5 10h17M5 4.75h14a1.75 1.75 0 0 1 1.75 1.75v12A1.75 1.75 0 0 1 19 20.25H5A1.75 1.75 0 0 1 3.25 18.5v-12A1.75 1.75 0 0 1 5 4.75Z" />
                            </svg>
                            <span class="text-sm font-semibold leading-none md:text-base">{{ $createdAtLabel }}</span>
                        </div>

                        <div class="mt-2 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                            <p class="text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Mascota:</span> {{ $petName }} ({{ $petTypeLabel }}, {{ $petSexLabel }})</p>
                            <p class="text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Solicitante:</span> {{ $applicantName }}</p>
                        </div>

                        <p class="mt-2 text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Contacto:</span> {{ $contactEmail }}</p>
                        <p class="mt-1 text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Teléfono:</span> {{ $contactPhone }}</p>
                        <p class="mt-1 text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Ciudad:</span> {{ $city }}</p>
                        <p class="mt-2 text-sm text-gray-700 md:text-base"><span class="font-semibold text-gray-800">Nota del usuario:</span> {{ $requestMessage }}</p>
                        @if($reviewerNote !== '')
                            <p class="mt-1 whitespace-pre-line rounded-lg border border-blue-100 bg-blue-50 px-2 py-1 text-xs text-blue-900 md:text-sm">
                                <span class="font-semibold">Tu nota al ciudadano:</span> {{ $reviewerNote }}
                            </p>
                        @endif

                        <details id="{{ $detailId }}" class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <summary class="cursor-pointer text-sm font-semibold text-gray-700">Ver informacion adicional</summary>

                            <div class="mt-3 grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2">
                                <div>
                                    <p class="font-semibold text-gray-800">Hogar</p>
                                    <p class="mt-1"><span class="font-medium">Vivienda:</span> {{ ucfirst((string) ($item['tipoVivienda'] ?? 'No disponible')) }}</p>
                                    <p><span class="font-medium">Patio o jardin:</span> {{ strtoupper((string) ($item['patioJardin'] ?? 'No')) }}</p>
                                    <p>
                                        <span class="font-medium">Integrantes:</span>
                                        {{ !empty($hogarIntegrantes) ? implode(', ', $hogarIntegrantes) : 'No especificado' }}
                                    </p>
                                    @if(!empty($item['hogarIntegrantesOtros']))
                                        <p><span class="font-medium">Otros:</span> {{ $item['hogarIntegrantesOtros'] }}</p>
                                    @endif
                                </div>

                                <div>
                                    <p class="font-semibold text-gray-800">Experiencia y otros animales</p>
                                    <p class="mt-1"><span class="font-medium">Ha tenido mascotas:</span> {{ strtoupper((string) ($item['tuvoMascotasAntes'] ?? 'No')) }}</p>
                                    @if(!empty($item['detalleMascotasAnteriores']))
                                        <p>{{ $item['detalleMascotasAnteriores'] }}</p>
                                    @endif
                                    <p class="mt-1"><span class="font-medium">Experiencia previa:</span> {{ $item['experienciaMascotas'] ?? 'No especificada' }}</p>
                                    <p class="mt-2"><span class="font-medium">Tiene otros animales:</span> {{ strtoupper((string) ($item['tieneOtrosAnimales'] ?? 'No')) }}</p>
                                    @if(!empty($item['tiposOtrosAnimales']))
                                        <p><span class="font-medium">Tipos:</span> {{ $item['tiposOtrosAnimales'] }}</p>
                                    @endif
                                    @if(!empty($item['otrosAnimalesEsterilizados']))
                                        <p><span class="font-medium">Esterilizados:</span> {{ strtoupper((string) $item['otrosAnimalesEsterilizados']) }}</p>
                                    @endif
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="published-request-actions w-full shrink-0">
                        <div class="published-request-status mb-3 flex justify-center">
                            <span class="inline-flex items-center rounded-full px-4 py-1 text-sm font-semibold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        @if($requestId !== '' && ! $isCancelled && ! $isApproved && ! $isRejected)
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('requests.status', ['requestId' => $requestId]) }}" class="request-status-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="aprobada">
                                    <input type="hidden" name="reviewerNote" value="{{ $reviewerNote }}" class="reviewer-note-hidden">
                                    <button
                                        type="submit"
                                        class="w-full rounded-md bg-green-600 px-4 py-2 text-lg font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($isApproved) disabled @endif
                                    >
                                        Confirmar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('requests.status', ['requestId' => $requestId]) }}" class="request-status-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rechazada">
                                    <input type="hidden" name="reviewerNote" value="{{ $reviewerNote }}" class="reviewer-note-hidden">
                                    <button
                                        type="submit"
                                        class="w-full rounded-md bg-red-600 px-4 py-2 text-lg font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        @if($isRejected) disabled @endif
                                    >
                                        Rechazar
                                    </button>
                                </form>

                                <button
                                    type="button"
                                    class="w-full rounded-md bg-blue-100 px-4 py-2 text-lg font-semibold text-blue-800 hover:bg-blue-200"
                                    data-note-toggle-target="{{ $notePanelId }}"
                                    data-note-focus-target="{{ $noteTextareaId }}"
                                >
                                    Agregar nota
                                </button>
                            </div>

                            <div id="{{ $notePanelId }}" class="mt-3 hidden rounded-lg border border-blue-100 bg-blue-50 p-3">
                                <form method="POST" action="{{ route('requests.note', ['requestId' => $requestId]) }}" class="space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    <label for="{{ $noteTextareaId }}" class="block text-sm font-semibold text-blue-900">
                                        Nota para el ciudadano
                                    </label>
                                    <textarea
                                        id="{{ $noteTextareaId }}"
                                        name="reviewerNote"
                                        rows="4"
                                        maxlength="1000"
                                        required
                                        class="w-full rounded-lg border border-blue-200 bg-white px-3 py-2 text-sm text-gray-800 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >{{ $reviewerNote }}</textarea>

                                    <div class="note-form-actions flex items-center justify-end gap-2">
                                        <button
                                            type="button"
                                            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                                            data-note-cancel-target="{{ $notePanelId }}"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            type="submit"
                                            class="save-note-btn"
                                        >
                                            Guardar nota
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                                Esta solicitud ya no se puede gestionar.
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-lg border bg-white p-4 text-gray-600">
                Aun no has recibido solicitudes para tus publicaciones de adopcion.
            </div>
        @endforelse
    </div>

    <div id="publishedRequestsNoMatches" class="mt-4 hidden rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-800">
        No se encontraron solicitudes con esos filtros.
    </div>

    <style>
        .published-request-card {
            padding: 0.75rem !important;
            margin-bottom: 0.75rem;
        }
        .published-request-layout {
            gap: 0.75rem !important;
        }
        .save-note-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            border-radius: 0.375rem;
            border: 1px solid #1e3a8a;
            background: #1d4ed8;
            color: #ffffff;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            font-weight: 600;
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .save-note-btn:hover {
            background: #1e40af;
        }

        .save-note-btn:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 2px;
        }

        .save-note-btn:active {
            transform: translateY(1px);
        }

        @media (min-width: 560px) {
            .published-request-layout {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }

            .published-request-actions {
                width: 16rem;
                min-width: 16rem;
                flex: 0 0 16rem;
                align-self: flex-start;
            }

            .published-request-status {
                justify-content: flex-end;
            }

            .note-form-actions {
                align-items: center;
            }
        }
    </style>

    <script>
        // Sincronizar el valor de la nota con los formularios de Confirmar/Rechazar
        document.addEventListener('DOMContentLoaded', function () {
            const cards = document.querySelectorAll('.published-request-card');
            cards.forEach(card => {
                const noteInput = card.querySelector('textarea[name="reviewerNote"]');
                const hiddenInputs = card.querySelectorAll('input.reviewer-note-hidden');
                if (noteInput && hiddenInputs.length) {
                    // Actualizar los hidden cuando cambia el textarea
                    noteInput.addEventListener('input', function () {
                        hiddenInputs.forEach(h => h.value = noteInput.value);
                    });
                    // Inicializar hidden con el valor actual
                    hiddenInputs.forEach(h => h.value = noteInput.value);
                }
            });
        });
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

            const noteToggleButtons = Array.from(document.querySelectorAll('[data-note-toggle-target]'));
            const noteCancelButtons = Array.from(document.querySelectorAll('[data-note-cancel-target]'));

            // Validación: No permitir confirmar/rechazar sin nota
            const statusForms = Array.from(document.querySelectorAll('form[action*="requests.status"]'));
            statusForms.forEach((form) => {
                form.addEventListener('submit', function (e) {
                    // Buscar el textarea de nota relacionado a esta solicitud
                    // Buscar el input hidden con name=requestId o extraer del action
                    let noteInput = null;
                    // Buscar textarea dentro del mismo card
                    let card = form.closest('.published-request-card');
                    if (card) {
                        noteInput = card.querySelector('textarea[name="reviewerNote"]');
                    }
                    if (!noteInput || !noteInput.value.trim()) {
                        e.preventDefault();
                        alert('Debes agregar una nota antes de confirmar o rechazar la solicitud.');
                        // Abrir panel de nota si existe
                        if (card) {
                            const notePanel = card.querySelector('[id^="publishedRequestNotePanel_"]');
                            if (notePanel) notePanel.classList.remove('hidden');
                            if (noteInput) noteInput.focus();
                        }
                        return false;
                    }
                });
            });

            noteToggleButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const notePanelId = button.getAttribute('data-note-toggle-target');
                    if (!notePanelId) {
                        return;
                    }

                    const notePanel = document.getElementById(notePanelId);
                    if (!notePanel) {
                        return;
                    }

                    const shouldShow = notePanel.classList.contains('hidden');
                    notePanel.classList.toggle('hidden', !shouldShow);

                    if (shouldShow) {
                        const inputId = button.getAttribute('data-note-focus-target');
                        const noteInput = inputId ? document.getElementById(inputId) : null;
                        if (noteInput) {
                            noteInput.focus();
                            const end = noteInput.value.length;
                            noteInput.setSelectionRange(end, end);
                        }
                    }
                });
            });

            noteCancelButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const notePanelId = button.getAttribute('data-note-cancel-target');
                    if (!notePanelId) {
                        return;
                    }

                    const notePanel = document.getElementById(notePanelId);
                    if (!notePanel) {
                        return;
                    }

                    notePanel.classList.add('hidden');
                });
            });

            applyPublishedRequestsFilters();
        })();
    </script>
@endsection
